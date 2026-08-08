-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 08 Agu 2026 pada 03.31
-- Versi server: 8.0.30
-- Versi PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `u471548307_karismaerp`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cctv_tracking`
--

CREATE TABLE `cctv_tracking` (
  `id` int UNSIGNED NOT NULL,
  `tgl` date NOT NULL,
  `lokasi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_kamera` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_kamera` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Online','Offline') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Offline',
  `status_rekaman` enum('Terekam','Tidak') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tidak',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stockopname_master`
--

CREATE TABLE `stockopname_master` (
  `id` int NOT NULL,
  `kode_barang` varchar(30) NOT NULL,
  `nama_barang` text NOT NULL,
  `qty` int NOT NULL,
  `qty_pcs` int NOT NULL,
  `qty_box` int NOT NULL,
  `expired_date` text NOT NULL,
  `no_lot` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stockopname_master_box`
--

CREATE TABLE `stockopname_master_box` (
  `id` int NOT NULL,
  `kode_barang` varchar(30) NOT NULL,
  `nama_barang` text NOT NULL,
  `qty` int NOT NULL,
  `qty_pcs` int NOT NULL,
  `qty_box` int NOT NULL,
  `expired_date` text NOT NULL,
  `no_lot` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stockopname_master_item`
--

CREATE TABLE `stockopname_master_item` (
  `id` int NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `nama_barang` text NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `qty_box` int NOT NULL DEFAULT '0',
  `qty_pcs` int NOT NULL DEFAULT '0',
  `pending_qty` int NOT NULL DEFAULT '0',
  `pending_qty_pcs` int NOT NULL DEFAULT '0',
  `pending_qty_box` int NOT NULL DEFAULT '0',
  `dimensi` int NOT NULL DEFAULT '0',
  `expired_date` date NOT NULL,
  `no_lot` varchar(100) NOT NULL,
  `qrcode` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `input_source` varchar(50) DEFAULT NULL,
  `request_status` varchar(20) DEFAULT NULL,
  `qrcode_value` varchar(255) DEFAULT NULL,
  `qrcode_file` varchar(255) DEFAULT NULL,
  `qrcode_status` enum('PENDING','PROCESS','DONE','FAILED') NOT NULL DEFAULT 'PENDING',
  `qrcode_retry_flag` tinyint(1) NOT NULL DEFAULT '0',
  `qrcode_attempt_count` int NOT NULL DEFAULT '0',
  `qrcode_error_message` text,
  `qrcode_generated_at` datetime DEFAULT NULL,
  `qrcode_updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stockopname_master_manual_item`
--

CREATE TABLE `stockopname_master_manual_item` (
  `id` int NOT NULL,
  `source_id` int NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `nama_barang` text NOT NULL,
  `expired_date` date NOT NULL,
  `no_lot` varchar(100) NOT NULL,
  `dimensi` int NOT NULL DEFAULT '0',
  `qty` int NOT NULL DEFAULT '0',
  `qty_pcs` int NOT NULL DEFAULT '0',
  `qty_box` int NOT NULL DEFAULT '0',
  `status` enum('PENDING','APPROVED','REJECTED','ADDED','DONE','Manual Input','Request Master Item') NOT NULL DEFAULT 'PENDING',
  `requested_by` varchar(100) NOT NULL,
  `requested_at` datetime NOT NULL,
  `wilayah` int NOT NULL DEFAULT '0',
  `tim_opname` int NOT NULL DEFAULT '0',
  `reviewed_by` varchar(100) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stockopname_opname`
--

CREATE TABLE `stockopname_opname` (
  `id` int NOT NULL,
  `kode_barang` varchar(30) NOT NULL,
  `nama_barang` text NOT NULL,
  `expired_date` text NOT NULL,
  `qty` int NOT NULL,
  `qty_pcs` int NOT NULL,
  `qty_box` int NOT NULL,
  `nolot` text NOT NULL,
  `input_by` text NOT NULL,
  `input_at` text NOT NULL,
  `wilayah` int NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stockopname_opname_log`
--

CREATE TABLE `stockopname_opname_log` (
  `id` int NOT NULL,
  `opname_id` int NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `nama_barang` text,
  `expired_date` date DEFAULT NULL,
  `no_lot` varchar(100) DEFAULT NULL,
  `action_type` varchar(30) NOT NULL DEFAULT 'EDIT_QTY',
  `action` varchar(30) NOT NULL DEFAULT 'UPDATE',
  `changed_fields` text,
  `old_data` longtext,
  `new_data` longtext,
  `old_value` longtext,
  `new_value` longtext,
  `description` text,
  `created_by` varchar(100) NOT NULL DEFAULT 'system',
  `created_at` datetime DEFAULT NULL,
  `before_qty` int DEFAULT NULL,
  `after_qty` int DEFAULT NULL,
  `before_qty_box` int DEFAULT NULL,
  `after_qty_box` int DEFAULT NULL,
  `before_qty_pcs` int DEFAULT NULL,
  `after_qty_pcs` int DEFAULT NULL,
  `changed_by` varchar(100) NOT NULL,
  `changed_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stockopname_opname_manual`
--

CREATE TABLE `stockopname_opname_manual` (
  `id` int NOT NULL,
  `manual_master_id` int NOT NULL,
  `source_id` int NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `nama_barang` text NOT NULL,
  `expired_date` date NOT NULL,
  `no_lot` varchar(100) NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `qty_pcs` int NOT NULL DEFAULT '0',
  `qty_box` int NOT NULL DEFAULT '0',
  `input_by` varchar(100) NOT NULL,
  `input_at` datetime NOT NULL,
  `wilayah` int NOT NULL DEFAULT '0',
  `tim_opname` int NOT NULL DEFAULT '0',
  `input_source` varchar(30) NOT NULL DEFAULT 'manual',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stockopname_pending`
--

CREATE TABLE `stockopname_pending` (
  `id` int NOT NULL,
  `kd_do` text NOT NULL,
  `kd_faktur` varchar(25) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `nama_barang` text NOT NULL,
  `qty` int NOT NULL,
  `no_lot` text NOT NULL,
  `exp_date` text NOT NULL,
  `input_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(100) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `kode_barang` varchar(50) NOT NULL DEFAULT '',
  `qty_box` int NOT NULL DEFAULT '0',
  `qty_pcs` int NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stockopname_recyclebin_input`
--

CREATE TABLE `stockopname_recyclebin_input` (
  `id` int NOT NULL,
  `source_id` int NOT NULL COMMENT 'ID record stockopname_opname yang dihapus',
  `original_source_id` int DEFAULT NULL COMMENT 'Relasi source_id ke master barang',
  `kode_barang` varchar(50) NOT NULL,
  `nama_barang` text NOT NULL,
  `expired_date` date NOT NULL,
  `no_lot` varchar(100) NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `qty_box` int NOT NULL DEFAULT '0',
  `qty_pcs` int NOT NULL DEFAULT '0',
  `input_by` varchar(100) NOT NULL,
  `input_at` datetime DEFAULT NULL,
  `wilayah` int NOT NULL DEFAULT '0',
  `tim_opname` int NOT NULL DEFAULT '0',
  `scan_code` varchar(255) DEFAULT NULL,
  `original_created_at` datetime DEFAULT NULL,
  `original_updated_at` datetime DEFAULT NULL,
  `deleted_by` varchar(100) NOT NULL,
  `deleted_at` datetime NOT NULL,
  `delete_reason` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbar_archive`
--

CREATE TABLE `tbar_archive` (
  `id_archive` int NOT NULL,
  `bulan` varchar(255) NOT NULL,
  `id_user` int NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `nilai_asli` decimal(10,2) DEFAULT '0.00',
  `nilai_akhir` decimal(10,2) DEFAULT '0.00',
  `sp_id` int DEFAULT NULL,
  `sp_jenis` varchar(10) DEFAULT NULL,
  `sp_pengurangan` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbar_bobotkpi`
--

CREATE TABLE `tbar_bobotkpi` (
  `idbobotkpi` int NOT NULL,
  `id_user` int NOT NULL,
  `id_arcv` int NOT NULL,
  `bobotwhat` int NOT NULL,
  `bobothow` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbar_hows`
--

CREATE TABLE `tbar_hows` (
  `id_how` int NOT NULL,
  `id_user` int NOT NULL,
  `id_kpi` int NOT NULL,
  `tipe_how` enum('A','B') DEFAULT 'A',
  `p_how` text NOT NULL,
  `bobot` double NOT NULL,
  `target_omset` decimal(15,2) DEFAULT '0.00',
  `hasil` text NOT NULL,
  `nilai` double NOT NULL,
  `total` double NOT NULL,
  `edited_by` int DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbar_indikator_hows`
--

CREATE TABLE `tbar_indikator_hows` (
  `id_indikator` int NOT NULL,
  `id_how` int NOT NULL,
  `keterangan` text NOT NULL,
  `nilai` decimal(5,2) NOT NULL,
  `urutan` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` int DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbar_indikator_whats`
--

CREATE TABLE `tbar_indikator_whats` (
  `id_indikator` int NOT NULL,
  `id_what` int NOT NULL,
  `keterangan` text NOT NULL,
  `nilai` decimal(5,2) NOT NULL,
  `urutan` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` int DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbar_kpi`
--

CREATE TABLE `tbar_kpi` (
  `id` int NOT NULL,
  `id_arcv` int NOT NULL,
  `id_user` int NOT NULL,
  `poin` text NOT NULL,
  `bobot` double NOT NULL,
  `poin2` text NOT NULL,
  `bobot2` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbar_sp_archive`
--

CREATE TABLE `tbar_sp_archive` (
  `id_sp_archive` int NOT NULL,
  `id_archive` int NOT NULL,
  `id_user` int NOT NULL,
  `jenis_sp` varchar(10) NOT NULL,
  `nomor_sp` varchar(100) NOT NULL,
  `tanggal_sp` date NOT NULL,
  `alasan` text NOT NULL,
  `keterangan` text,
  `masa_berlaku_mulai` date NOT NULL,
  `masa_berlaku_selesai` date NOT NULL,
  `file_sp` varchar(255) DEFAULT NULL,
  `nilai_asli` decimal(10,2) NOT NULL,
  `nilai_akhir` decimal(10,2) NOT NULL,
  `pengurangan` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbar_whats`
--

CREATE TABLE `tbar_whats` (
  `id_what` int NOT NULL,
  `id_user` int NOT NULL,
  `id_kpi` int NOT NULL,
  `tipe_what` enum('A','B') DEFAULT 'A',
  `p_what` text NOT NULL,
  `bobot` double NOT NULL,
  `target_omset` decimal(15,2) DEFAULT '0.00',
  `hasil` text NOT NULL,
  `nilai` double NOT NULL,
  `total` double NOT NULL,
  `edited_by` int DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tberp_stock_batch`
--

CREATE TABLE `tberp_stock_batch` (
  `id` bigint NOT NULL,
  `kd_barang` varchar(50) DEFAULT NULL,
  `gudang_id` varchar(30) DEFAULT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `qty_on_hand` decimal(15,3) DEFAULT '0.000',
  `qty_reserved` decimal(15,3) DEFAULT '0.000',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tberp_stock_ledger`
--

CREATE TABLE `tberp_stock_ledger` (
  `id` bigint NOT NULL,
  `kd_barang` varchar(50) DEFAULT NULL,
  `gudang_id` varchar(30) DEFAULT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `qty` decimal(15,3) DEFAULT NULL,
  `tipe` enum('SALDO_AWAL','IN','RESERVE','RELEASE','OUT','RBELI','RJUAL','MUTASI','ADJIN','ADJOUT') DEFAULT NULL,
  `ref_no` varchar(50) DEFAULT NULL,
  `ref_type` varchar(30) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbhrd_environment_issues`
--

CREATE TABLE `tbhrd_environment_issues` (
  `id` bigint NOT NULL,
  `location_id` int NOT NULL,
  `rating_id` int NOT NULL,
  `description` text,
  `report_datetime` datetime NOT NULL,
  `due_date` date DEFAULT NULL,
  `status_id` int DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `star_rating` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbhrd_issue_evidences`
--

CREATE TABLE `tbhrd_issue_evidences` (
  `id` bigint NOT NULL,
  `issue_id` bigint DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbhrd_issue_logs`
--

CREATE TABLE `tbhrd_issue_logs` (
  `id` bigint NOT NULL,
  `issue_id` bigint DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `note` text,
  `changed_by` int DEFAULT NULL,
  `changed_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbhrd_issue_rating`
--

CREATE TABLE `tbhrd_issue_rating` (
  `id` int NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `score` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbhrd_issue_status`
--

CREATE TABLE `tbhrd_issue_status` (
  `id` int NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbhrd_lokasi`
--

CREATE TABLE `tbhrd_lokasi` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `is_active` tinyint DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbhrd_nilai_lingkungan`
--

CREATE TABLE `tbhrd_nilai_lingkungan` (
  `id` bigint NOT NULL,
  `location_id` int NOT NULL,
  `rating_id` int NOT NULL,
  `star_rating` tinyint(1) NOT NULL DEFAULT '0',
  `description` text,
  `report_datetime` datetime NOT NULL,
  `due_date` date DEFAULT NULL,
  `status_id` int DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_akun`
--

CREATE TABLE `tbkeu_akun` (
  `id_akun` bigint UNSIGNED NOT NULL,
  `kode_akun` varchar(30) NOT NULL,
  `nama_akun` varchar(150) NOT NULL,
  `id_klasifikasi` tinyint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `level_akun` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `saldo_normal` varchar(20) NOT NULL,
  `tipe_akun` enum('HEADER','POSTING') NOT NULL DEFAULT 'POSTING',
  `tipe_kontrol` varchar(50) NOT NULL DEFAULT 'NONE',
  `allow_manual_journal` tinyint(1) NOT NULL DEFAULT '1',
  `is_transaction_eligible` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_akun_karismaerp_ref`
--

CREATE TABLE `tbkeu_akun_karismaerp_ref` (
  `id_ref` bigint UNSIGNED NOT NULL,
  `id_batch` bigint UNSIGNED DEFAULT NULL,
  `id_akun` bigint UNSIGNED DEFAULT NULL,
  `id_sub_klasifikasi` bigint UNSIGNED DEFAULT NULL,
  `kode_kiraan` varchar(30) NOT NULL,
  `kode_rekening_display` varchar(30) DEFAULT NULL,
  `nama_karismaerp` varchar(150) DEFAULT NULL,
  `alias_karismaerp` varchar(150) DEFAULT NULL,
  `temp_nama_karismaerp` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_dummy_source`
--

CREATE TABLE `tbkeu_dummy_source` (
  `id_dummy` bigint UNSIGNED NOT NULL,
  `posting_event` varchar(50) NOT NULL,
  `source_no` varchar(100) NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `partner_name` varchar(150) DEFAULT NULL,
  `product_name` varchar(150) DEFAULT NULL,
  `qty` decimal(18,2) NOT NULL DEFAULT '0.00',
  `unit_name` varchar(30) DEFAULT NULL,
  `unit_price` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `warehouse_name` varchar(100) DEFAULT NULL,
  `amount` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `tax` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `cogs` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_jenis_jurnal`
--

CREATE TABLE `tbkeu_jenis_jurnal` (
  `id_jenis_jurnal` smallint UNSIGNED NOT NULL,
  `kode_jenis_jurnal` varchar(20) NOT NULL,
  `nama_jenis_jurnal` varchar(100) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `is_manual` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_jurnal`
--

CREATE TABLE `tbkeu_jurnal` (
  `id_jurnal` bigint UNSIGNED NOT NULL,
  `nomor_jurnal` varchar(50) NOT NULL,
  `id_jenis_jurnal` smallint UNSIGNED DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `id_periode` bigint UNSIGNED DEFAULT NULL,
  `keterangan` varchar(500) DEFAULT NULL,
  `source_module` varchar(50) DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` varchar(100) DEFAULT NULL,
  `source_no` varchar(100) DEFAULT NULL,
  `posting_event` varchar(50) DEFAULT NULL,
  `status` enum('DRAFT','POSTED','REVERSED','VOID') NOT NULL DEFAULT 'DRAFT',
  `total_debit` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `total_kredit` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `reversal_of_journal_id` bigint UNSIGNED DEFAULT NULL,
  `idempotency_key` varchar(255) DEFAULT NULL,
  `created_by` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `posted_by` bigint DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `reversed_by` bigint DEFAULT NULL,
  `reversed_at` datetime DEFAULT NULL,
  `voided_by` bigint DEFAULT NULL,
  `voided_at` datetime DEFAULT NULL,
  `void_reason` varchar(500) DEFAULT NULL,
  `lock_version` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_jurnal_detail`
--

CREATE TABLE `tbkeu_jurnal_detail` (
  `id_jurnal_detail` bigint UNSIGNED NOT NULL,
  `id_jurnal` bigint UNSIGNED NOT NULL,
  `nomor_baris` smallint UNSIGNED NOT NULL,
  `id_akun` bigint UNSIGNED NOT NULL,
  `keterangan` varchar(500) DEFAULT NULL,
  `debit` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `kredit` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `id_customer` bigint DEFAULT NULL,
  `id_supplier` bigint DEFAULT NULL,
  `id_barang` bigint DEFAULT NULL,
  `id_gudang` bigint DEFAULT NULL,
  `id_departemen` bigint DEFAULT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `nomor_dokumen` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_jurnal_log`
--

CREATE TABLE `tbkeu_jurnal_log` (
  `id_log` bigint UNSIGNED NOT NULL,
  `id_jurnal` bigint UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL,
  `message` varchar(500) DEFAULT NULL,
  `created_by` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_karismaerp_import_batch`
--

CREATE TABLE `tbkeu_karismaerp_import_batch` (
  `id_batch` bigint UNSIGNED NOT NULL,
  `batch_code` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_hash` varchar(64) DEFAULT NULL,
  `status` enum('DRAFT','VALIDATED','APPLIED','FAILED','CANCELLED') NOT NULL DEFAULT 'APPLIED',
  `total_kelompok` int UNSIGNED NOT NULL DEFAULT '0',
  `total_klasifikasi` int UNSIGNED NOT NULL DEFAULT '0',
  `total_sub_class` int UNSIGNED NOT NULL DEFAULT '0',
  `total_kiraan` int UNSIGNED NOT NULL DEFAULT '0',
  `total_rule_laba_rugi` int UNSIGNED NOT NULL DEFAULT '0',
  `total_rule_neraca` int UNSIGNED NOT NULL DEFAULT '0',
  `validation_message` text,
  `created_by` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `applied_by` bigint DEFAULT NULL,
  `applied_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_karismaerp_kelompok_jurnal`
--

CREATE TABLE `tbkeu_karismaerp_kelompok_jurnal` (
  `id_kelompok_karismaerp` bigint UNSIGNED NOT NULL,
  `id_batch` bigint UNSIGNED DEFAULT NULL,
  `no_kelompok` int UNSIGNED NOT NULL,
  `kode_kelompok` varchar(20) NOT NULL,
  `nama_kelompok` varchar(150) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `is_auto_source` tinyint(1) NOT NULL DEFAULT '0',
  `simbol` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_kas_keluar`
--

CREATE TABLE `tbkeu_kas_keluar` (
  `id_kas_keluar` bigint UNSIGNED NOT NULL,
  `no_referensi` varchar(50) NOT NULL,
  `id_akun_kas` bigint UNSIGNED NOT NULL,
  `penerima` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `memo` varchar(500) DEFAULT NULL,
  `is_inclusive_tax` tinyint(1) DEFAULT '0',
  `is_giro` tinyint(1) DEFAULT '0',
  `total_amount` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `status` enum('DRAFT','POSTED') NOT NULL DEFAULT 'DRAFT',
  `id_jurnal` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_kas_keluar_detail`
--

CREATE TABLE `tbkeu_kas_keluar_detail` (
  `id_kas_keluar_detail` bigint UNSIGNED NOT NULL,
  `id_kas_keluar` bigint UNSIGNED NOT NULL,
  `id_akun` bigint UNSIGNED NOT NULL,
  `nilai` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `nomor_baris` smallint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_kas_masuk`
--

CREATE TABLE `tbkeu_kas_masuk` (
  `id_kas_masuk` bigint UNSIGNED NOT NULL,
  `no_referensi` varchar(50) NOT NULL,
  `id_akun_kas` bigint UNSIGNED NOT NULL,
  `diterima_dari` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `memo` varchar(500) DEFAULT NULL,
  `is_inclusive_tax` tinyint(1) DEFAULT '0',
  `is_giro` tinyint(1) DEFAULT '0',
  `total_amount` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `status` enum('DRAFT','POSTED') NOT NULL DEFAULT 'DRAFT',
  `id_jurnal` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_kas_masuk_detail`
--

CREATE TABLE `tbkeu_kas_masuk_detail` (
  `id_kas_masuk_detail` bigint UNSIGNED NOT NULL,
  `id_kas_masuk` bigint UNSIGNED NOT NULL,
  `id_akun` bigint UNSIGNED NOT NULL,
  `nilai` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `nomor_baris` smallint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_kelompok_dagang`
--

CREATE TABLE `tbkeu_kelompok_dagang` (
  `NOINDEX` int NOT NULL,
  `DESKRIPSI` varchar(150) DEFAULT NULL,
  `KODESALES` varchar(20) DEFAULT NULL,
  `KODEINVENTORI` varchar(20) DEFAULT NULL,
  `KODEHARGAPOKOK` varchar(20) DEFAULT NULL,
  `KODEKONSINYASI` varchar(20) DEFAULT NULL,
  `DEPT` varchar(20) DEFAULT NULL,
  `KODERETUR` varchar(20) DEFAULT NULL,
  `GUDANG` varchar(50) DEFAULT NULL,
  `INVENTORI` char(1) DEFAULT NULL,
  `BELI` char(1) DEFAULT NULL,
  `JUAL` char(1) DEFAULT NULL,
  `SISTEM` tinyint(1) NOT NULL DEFAULT '0',
  `TANGGALEDIT` datetime DEFAULT NULL,
  `IMAGE` varchar(255) DEFAULT NULL,
  `KODEPENGIRIMANBELI` varchar(20) DEFAULT NULL,
  `KODEPENGIRIMANJUAL` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_klasifikasi_akun`
--

CREATE TABLE `tbkeu_klasifikasi_akun` (
  `id_klasifikasi` tinyint UNSIGNED NOT NULL,
  `kode_klasifikasi` varchar(10) NOT NULL,
  `nama_klasifikasi` varchar(100) NOT NULL,
  `alias_klasifikasi` varchar(100) DEFAULT NULL,
  `jenis_laporan` enum('NERACA','LABA_RUGI') NOT NULL,
  `saldo_normal` varchar(20) NOT NULL,
  `urutan` smallint UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_mapping_akun`
--

CREATE TABLE `tbkeu_mapping_akun` (
  `id_mapping` bigint UNSIGNED NOT NULL,
  `source_module` varchar(50) NOT NULL DEFAULT '*',
  `source_type` varchar(50) NOT NULL DEFAULT '*',
  `scope_type` varchar(30) NOT NULL DEFAULT 'GLOBAL',
  `scope_key` varchar(100) NOT NULL DEFAULT '*',
  `posting_event` varchar(50) NOT NULL,
  `account_role` varchar(80) NOT NULL,
  `entry_side` enum('DEBIT','KREDIT','ANY') NOT NULL DEFAULT 'ANY',
  `id_akun` bigint UNSIGNED NOT NULL,
  `priority` smallint UNSIGNED NOT NULL DEFAULT '100',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `keterangan` varchar(255) DEFAULT NULL,
  `created_by` bigint DEFAULT NULL,
  `updated_by` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_nomor_dokumen`
--

CREATE TABLE `tbkeu_nomor_dokumen` (
  `id_nomor` bigint UNSIGNED NOT NULL,
  `kode_jenis_jurnal` varchar(20) NOT NULL,
  `periode_yyyymm` char(6) NOT NULL,
  `last_number` int UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_pembayaran`
--

CREATE TABLE `tbkeu_pembayaran` (
  `id_pembayaran` bigint UNSIGNED NOT NULL,
  `payment_type` enum('CUSTOMER_PAYMENT','SUPPLIER_PAYMENT') NOT NULL,
  `nomor_pembayaran` varchar(100) NOT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `source_module` varchar(50) DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` varchar(100) DEFAULT NULL,
  `source_no` varchar(100) DEFAULT NULL,
  `id_customer` bigint DEFAULT NULL,
  `id_supplier` bigint DEFAULT NULL,
  `amount` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `allocated_amount` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `unapplied_amount` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `status` enum('DRAFT','POSTED','VOID') NOT NULL DEFAULT 'DRAFT',
  `id_jurnal` bigint UNSIGNED DEFAULT NULL,
  `keterangan` varchar(500) DEFAULT NULL,
  `created_by` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_pembayaran_alokasi`
--

CREATE TABLE `tbkeu_pembayaran_alokasi` (
  `id_alokasi` bigint UNSIGNED NOT NULL,
  `id_pembayaran` bigint UNSIGNED NOT NULL,
  `nomor_baris` smallint UNSIGNED NOT NULL,
  `invoice_source_module` varchar(50) DEFAULT NULL,
  `invoice_source_type` varchar(50) DEFAULT NULL,
  `invoice_source_id` varchar(100) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `amount_allocated` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `keterangan` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_pembayaran_faktur`
--

CREATE TABLE `tbkeu_pembayaran_faktur` (
  `id_pembayaran` int UNSIGNED NOT NULL,
  `id_faktur` int UNSIGNED NOT NULL,
  `no_faktur` varchar(30) NOT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `jumlah_pembayaran` decimal(16,2) NOT NULL DEFAULT '0.00',
  `jumlah_diskon` decimal(16,2) DEFAULT '0.00',
  `metode_pembayaran` varchar(100) DEFAULT NULL,
  `cara_pembayaran` varchar(50) DEFAULT NULL,
  `tanggal_bg_cair` date DEFAULT NULL,
  `no_bg` varchar(50) DEFAULT NULL,
  `nama_bank` varchar(100) DEFAULT NULL,
  `status_bg` varchar(20) NOT NULL DEFAULT 'not_bg',
  `status_kasir` varchar(20) NOT NULL DEFAULT 'valid',
  `kasir_approved_by` varchar(100) DEFAULT NULL,
  `kasir_approved_at` datetime DEFAULT NULL,
  `bg_cair_by` varchar(100) DEFAULT NULL,
  `bg_cair_at` datetime DEFAULT NULL,
  `keterangan` text,
  `create_by` varchar(100) DEFAULT NULL,
  `create_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_periode_fiskal`
--

CREATE TABLE `tbkeu_periode_fiskal` (
  `id_periode` bigint UNSIGNED NOT NULL,
  `kode_periode` varchar(20) NOT NULL,
  `nama_periode` varchar(100) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('OPEN','CLOSED') NOT NULL DEFAULT 'OPEN',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `closed_by` bigint DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `reopened_by` bigint DEFAULT NULL,
  `reopened_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_periode_fiskal_log`
--

CREATE TABLE `tbkeu_periode_fiskal_log` (
  `id_log` bigint UNSIGNED NOT NULL,
  `id_periode` bigint UNSIGNED NOT NULL,
  `action` enum('OPEN','CLOSE','REOPEN') NOT NULL,
  `reason` varchar(500) NOT NULL,
  `approval_by` bigint DEFAULT NULL,
  `approval_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_posting_exception`
--

CREATE TABLE `tbkeu_posting_exception` (
  `id_exception` bigint UNSIGNED NOT NULL,
  `source_module` varchar(50) DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` varchar(100) DEFAULT NULL,
  `source_no` varchar(100) DEFAULT NULL,
  `posting_event` varchar(50) DEFAULT NULL,
  `error_code` varchar(80) NOT NULL,
  `error_message` varchar(1000) NOT NULL,
  `payload_json` longtext,
  `status` enum('OPEN','RESOLVED','IGNORED') NOT NULL DEFAULT 'OPEN',
  `retry_count` int UNSIGNED NOT NULL DEFAULT '0',
  `occurrence_count` int UNSIGNED NOT NULL DEFAULT '1',
  `last_occurred_at` datetime DEFAULT NULL,
  `last_retry_at` datetime DEFAULT NULL,
  `id_jurnal` bigint UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `resolved_by` bigint DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `resolution_note` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_report_rule_akun`
--

CREATE TABLE `tbkeu_report_rule_akun` (
  `id_rule` bigint UNSIGNED NOT NULL,
  `id_batch` bigint UNSIGNED DEFAULT NULL,
  `statement_type` enum('LABA_RUGI','NERACA') NOT NULL,
  `id_akun` bigint UNSIGNED DEFAULT NULL,
  `kode_rekening_source` varchar(30) NOT NULL,
  `kode_rekening_normalized` varchar(30) NOT NULL,
  `nama_rekening_source` varchar(150) DEFAULT NULL,
  `klasifikasi_source` varchar(100) DEFAULT NULL,
  `group_source` varchar(150) DEFAULT NULL,
  `bertambah_side` enum('DEBIT','KREDIT') NOT NULL,
  `berkurang_side` enum('DEBIT','KREDIT') NOT NULL,
  `urutan` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_saldo_awal_akun`
--

CREATE TABLE `tbkeu_saldo_awal_akun` (
  `id_saldo_awal` bigint UNSIGNED NOT NULL,
  `id_akun` bigint UNSIGNED NOT NULL,
  `tanggal_saldo` date NOT NULL,
  `debit` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `kredit` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `keterangan` varchar(255) DEFAULT NULL,
  `is_migrated` tinyint(1) NOT NULL DEFAULT '0',
  `id_jurnal` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `migrated_by` bigint DEFAULT NULL,
  `migrated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_saldo_normal`
--

CREATE TABLE `tbkeu_saldo_normal` (
  `kode_saldo` varchar(20) NOT NULL,
  `nama_saldo` varchar(50) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `urutan` smallint UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_sub_klasifikasi_akun`
--

CREATE TABLE `tbkeu_sub_klasifikasi_akun` (
  `id_sub_klasifikasi` bigint UNSIGNED NOT NULL,
  `id_batch` bigint UNSIGNED DEFAULT NULL,
  `kode_sub_klasifikasi` varchar(30) NOT NULL,
  `id_klasifikasi` tinyint UNSIGNED NOT NULL,
  `nama_sub_klasifikasi` varchar(150) NOT NULL,
  `alias_sub_klasifikasi` varchar(150) DEFAULT NULL,
  `nama_temp_karismaerp` varchar(150) DEFAULT NULL,
  `cashflow_id` varchar(30) DEFAULT NULL,
  `tanggal_edit_source` varchar(50) DEFAULT NULL,
  `id_akun_header` bigint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkeu_tipe_kontrol`
--

CREATE TABLE `tbkeu_tipe_kontrol` (
  `kode_tipe_kontrol` varchar(50) NOT NULL,
  `nama_tipe_kontrol` varchar(100) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `urutan` smallint UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_dca`
--

CREATE TABLE `tbkmt_dca` (
  `id` int NOT NULL,
  `tanggal_dca` date NOT NULL,
  `bulan` tinyint NOT NULL,
  `tahun` year NOT NULL,
  `id_wilayah` int NOT NULL,
  `nama_mdo` varchar(100) DEFAULT NULL,
  `abm` varchar(100) DEFAULT NULL,
  `uraian` varchar(255) DEFAULT NULL,
  `um` decimal(15,2) DEFAULT '0.00',
  `refund` decimal(15,2) DEFAULT '0.00',
  `real_biaya` decimal(15,2) DEFAULT '0.00',
  `total_biaya` decimal(15,2) DEFAULT '0.00',
  `status_verifikasi` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=Belum Diverifikasi, 1=Sudah Diverifikasi',
  `verified_by` int DEFAULT NULL COMMENT 'id_user yang melakukan verifikasi (level 2 / adm keu)',
  `verified_at` datetime DEFAULT NULL COMMENT 'Waktu verifikasi dilakukan',
  `verified_notes` varchar(255) DEFAULT NULL COMMENT 'Catatan dari adm keu saat verifikasi',
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_dca_detail`
--

CREATE TABLE `tbkmt_dca_detail` (
  `id` int NOT NULL,
  `id_dca` int NOT NULL,
  `id_kegiatan` int DEFAULT NULL,
  `nama_kegiatan` varchar(150) NOT NULL,
  `tgl_kegiatan` date DEFAULT NULL,
  `tgl_kasbon` date DEFAULT NULL,
  `jml_peserta` int DEFAULT '0',
  `qty_bisi` decimal(10,2) DEFAULT '0.00',
  `qty_q235` decimal(10,2) DEFAULT '0.00',
  `um` decimal(15,2) DEFAULT '0.00',
  `refund` decimal(15,2) DEFAULT '0.00',
  `real_biaya` decimal(15,2) DEFAULT '0.00',
  `total_biaya` decimal(15,2) DEFAULT '0.00',
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_dca_kegiatan`
--

CREATE TABLE `tbkmt_dca_kegiatan` (
  `id` int NOT NULL,
  `nama_kegiatan` varchar(150) NOT NULL,
  `is_custom` tinyint(1) DEFAULT '0' COMMENT '0=master bawaan, 1=tambahan user',
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_dca_verifikasi_log`
--

CREATE TABLE `tbkmt_dca_verifikasi_log` (
  `id` int NOT NULL,
  `id_dca` int NOT NULL,
  `aksi` varchar(50) NOT NULL COMMENT 'verifikasi / batal_verifikasi',
  `id_user` int NOT NULL,
  `nama_user` varchar(100) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Log riwayat verifikasi DCA KMT CORN';

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_gaji`
--

CREATE TABLE `tbkmt_gaji` (
  `id` int NOT NULL,
  `id_wilayah` int NOT NULL,
  `nama` varchar(150) NOT NULL,
  `posisi` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_resign` date DEFAULT NULL,
  `gaji_jan` decimal(15,2) DEFAULT '0.00',
  `gaji_feb` decimal(15,2) DEFAULT '0.00',
  `gaji_mar` decimal(15,2) DEFAULT '0.00',
  `gaji_apr` decimal(15,2) DEFAULT '0.00',
  `gaji_mei` decimal(15,2) DEFAULT '0.00',
  `gaji_jun` decimal(15,2) DEFAULT '0.00',
  `gaji_jul` decimal(15,2) DEFAULT '0.00',
  `gaji_agu` decimal(15,2) DEFAULT '0.00',
  `gaji_sep` decimal(15,2) DEFAULT '0.00',
  `gaji_okt` decimal(15,2) DEFAULT '0.00',
  `gaji_nov` decimal(15,2) DEFAULT '0.00',
  `gaji_des` decimal(15,2) DEFAULT '0.00',
  `tahun` year NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_omset`
--

CREATE TABLE `tbkmt_omset` (
  `id` int NOT NULL,
  `no_urut` varchar(20) DEFAULT NULL,
  `kode` varchar(20) DEFAULT NULL,
  `bulan` tinyint NOT NULL,
  `tahun` year NOT NULL,
  `tanggal` date DEFAULT NULL,
  `nomor` varchar(50) DEFAULT NULL,
  `inputer` varchar(100) DEFAULT NULL,
  `no_retur` varchar(50) DEFAULT NULL,
  `tgl_retur` date DEFAULT NULL,
  `sales_so` varchar(100) DEFAULT NULL,
  `sc` varchar(100) DEFAULT NULL,
  `se` varchar(100) DEFAULT NULL,
  `wilayah_se` varchar(100) DEFAULT NULL,
  `kontak_person` varchar(150) DEFAULT NULL,
  `alamat` text,
  `id_wilayah` int NOT NULL,
  `nama_toko` varchar(200) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `merk` varchar(100) DEFAULT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `golongan` varchar(100) DEFAULT NULL,
  `point` decimal(10,2) DEFAULT '0.00',
  `fokus` varchar(100) DEFAULT NULL,
  `kode_produk` varchar(50) DEFAULT NULL,
  `produk` varchar(200) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT '0.00',
  `unit` varchar(20) DEFAULT NULL,
  `box` decimal(10,2) DEFAULT '0.00',
  `ltr_kg` decimal(10,2) DEFAULT '0.00',
  `harga_inc_ppn` decimal(15,2) DEFAULT '0.00',
  `penj_dpp_neto` decimal(15,2) DEFAULT '0.00',
  `penj_inc_ppn_neto` decimal(15,2) DEFAULT '0.00',
  `keterangan` text,
  `tgl_kirim` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_operasional`
--

CREATE TABLE `tbkmt_operasional` (
  `id` int NOT NULL,
  `id_wilayah` int NOT NULL,
  `nama_mdo` varchar(100) DEFAULT NULL,
  `um` decimal(15,2) DEFAULT '0.00',
  `refund` decimal(15,2) DEFAULT '0.00',
  `bulan` tinyint NOT NULL,
  `tahun` year NOT NULL,
  `tanggal` date NOT NULL,
  `nama` varchar(100) NOT NULL,
  `hotel` decimal(15,2) DEFAULT '0.00',
  `per_diem` decimal(15,2) DEFAULT '0.00',
  `entertainment` decimal(15,2) DEFAULT '0.00',
  `communication` decimal(15,2) DEFAULT '0.00',
  `atk` decimal(15,2) DEFAULT '0.00',
  `gasoline` decimal(15,2) DEFAULT '0.00',
  `sparepart_service` decimal(15,2) DEFAULT '0.00',
  `retribusi_toll_parkir` decimal(15,2) DEFAULT '0.00',
  `transportasi` decimal(15,2) DEFAULT '0.00',
  `pos_paket` decimal(15,2) DEFAULT '0.00',
  `tambah_angin` decimal(15,2) DEFAULT '0.00',
  `tambal_ban` decimal(15,2) DEFAULT '0.00',
  `indekost` decimal(15,2) DEFAULT '0.00',
  `sewa_kendaraan` decimal(15,2) DEFAULT '0.00',
  `lain_lain` decimal(15,2) DEFAULT '0.00',
  `total_biaya` decimal(15,2) GENERATED ALWAYS AS ((((((((((((((`hotel` + `per_diem`) + `entertainment`) + `communication`) + `atk`) + `gasoline`) + `sparepart_service`) + `retribusi_toll_parkir`) + `transportasi`) + `pos_paket`) + `tambah_angin`) + `tambal_ban`) + `indekost`) + `lain_lain`)) STORED,
  `status_verifikasi` tinyint(1) NOT NULL DEFAULT '0',
  `verified_by` int DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `verified_notes` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_operasional_verifikasi_log`
--

CREATE TABLE `tbkmt_operasional_verifikasi_log` (
  `id` int NOT NULL,
  `id_operasional` int NOT NULL,
  `aksi` varchar(50) NOT NULL COMMENT 'verifikasi / batal_verifikasi',
  `id_user` int NOT NULL,
  `nama_user` varchar(100) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Log riwayat verifikasi Operasional KMT CORN';

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_others`
--

CREATE TABLE `tbkmt_others` (
  `id` int NOT NULL,
  `id_wilayah` int NOT NULL,
  `bulan` tinyint NOT NULL,
  `tahun` year NOT NULL,
  `tanggal` date NOT NULL,
  `uraian` varchar(255) NOT NULL,
  `total_biaya` decimal(15,2) DEFAULT '0.00',
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_promo_material`
--

CREATE TABLE `tbkmt_promo_material` (
  `id` int NOT NULL,
  `id_wilayah` int NOT NULL,
  `bulan` tinyint NOT NULL,
  `tahun` year NOT NULL,
  `tanggal` date NOT NULL,
  `supplier` varchar(200) DEFAULT NULL,
  `nama_item` varchar(200) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `qty` int DEFAULT '1',
  `satuan` varchar(50) DEFAULT NULL,
  `harga_satuan` decimal(15,2) DEFAULT '0.00',
  `total_biaya` decimal(15,2) DEFAULT '0.00',
  `promo_material` decimal(15,2) DEFAULT '0.00',
  `peralatan` decimal(15,2) DEFAULT '0.00',
  `keterangan` text,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_retur`
--

CREATE TABLE `tbkmt_retur` (
  `id` int NOT NULL,
  `id_omset` int DEFAULT NULL,
  `id_wilayah` int NOT NULL,
  `no_lpb` varchar(50) DEFAULT NULL,
  `bulan` tinyint NOT NULL,
  `tahun` year NOT NULL,
  `tanggal_retur` date NOT NULL,
  `no_retur` varchar(50) DEFAULT NULL,
  `tgl_fak_retur` date DEFAULT NULL,
  `no_faktur` varchar(50) DEFAULT NULL,
  `nama_toko` varchar(200) DEFAULT NULL,
  `kontak_person` varchar(150) DEFAULT NULL,
  `alamat` text,
  `produk` varchar(200) DEFAULT NULL,
  `sc` varchar(100) DEFAULT NULL,
  `kode_toko` varchar(20) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `merk` varchar(100) DEFAULT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `golongan` varchar(50) DEFAULT NULL,
  `prod` varchar(100) DEFAULT NULL,
  `point` decimal(10,2) DEFAULT '0.00',
  `fokus` varchar(50) DEFAULT NULL,
  `kode_produk` varchar(20) DEFAULT NULL,
  `harga_dpp` decimal(15,2) DEFAULT '0.00',
  `quantity` decimal(10,2) DEFAULT '0.00',
  `unit` varchar(50) DEFAULT NULL,
  `nilai_retur` decimal(15,2) DEFAULT '0.00',
  `kurangi_target` tinyint(1) DEFAULT '0' COMMENT '1=kurangi target ABM, 0=tidak',
  `keterangan` text,
  `keterangan_detail` text,
  `kategori` varchar(100) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbkmt_wilayah`
--

CREATE TABLE `tbkmt_wilayah` (
  `id` int NOT NULL,
  `kode_wilayah` varchar(10) NOT NULL,
  `nama_wilayah` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_akun_tr`
--

CREATE TABLE `tbpo_akun_tr` (
  `id_akun` int NOT NULL,
  `kd_akun` varchar(25) NOT NULL,
  `ket_akun` text NOT NULL,
  `input_by` varchar(25) NOT NULL,
  `create_at` datetime NOT NULL,
  `update_by` varchar(25) NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_barang`
--

CREATE TABLE `tbpo_barang` (
  `id_barang` int NOT NULL,
  `kode_barang` varchar(25) NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `nama_barang` text NOT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `panjang` int NOT NULL DEFAULT '0',
  `lebar` int NOT NULL DEFAULT '0',
  `tinggi` int NOT NULL DEFAULT '0',
  `berat` decimal(15,2) NOT NULL DEFAULT '0.00',
  `kubikasi` decimal(12,6) NOT NULL DEFAULT '0.000000',
  `isi` decimal(15,2) NOT NULL DEFAULT '0.00',
  `kemasan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stock_minimum` int NOT NULL DEFAULT '0',
  `merk_barang` text NOT NULL,
  `kelompok_dagang` text,
  `kelompok_barang` text,
  `kategori_barang` text,
  `bhn_aktif` text,
  `produk_fokus` text,
  `is_active` enum('T','F') NOT NULL DEFAULT 'T',
  `is_lot` enum('T','F') NOT NULL DEFAULT 'F',
  `is_inventori` enum('T','F') NOT NULL DEFAULT 'T',
  `is_beli` enum('T','F') NOT NULL DEFAULT 'T',
  `is_jual` enum('T','F') NOT NULL DEFAULT 'T',
  `hpp_average` enum('T','F') NOT NULL DEFAULT 'T',
  `hpp_fifo` enum('T','F') NOT NULL DEFAULT 'F',
  `hpp_lifo` enum('T','F') NOT NULL DEFAULT 'F',
  `kode_akun_harga_pokok` varchar(30) DEFAULT '51030',
  `kode_akun_penjualan` varchar(30) DEFAULT '41032',
  `kode_akun_persediaan` varchar(30) DEFAULT '14030',
  `kode_akun_pengiriman_beli` varchar(30) DEFAULT '51032',
  `kode_akun_pengiriman_jual` varchar(30) DEFAULT '64030',
  `kode_akun_retur_penjualan` varchar(30) DEFAULT '41034'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_barang_akun`
--

CREATE TABLE `tbpo_barang_akun` (
  `kode_barang` varchar(30) NOT NULL,
  `kode_akun_penjualan` varchar(30) DEFAULT NULL,
  `kode_akun_persediaan` varchar(30) DEFAULT NULL,
  `kode_akun_harga_pokok` varchar(30) DEFAULT NULL,
  `kode_akun_retur_penjualan` varchar(30) DEFAULT NULL,
  `kode_akun_pengiriman_beli` varchar(30) DEFAULT NULL,
  `kode_akun_pengiriman_jual` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_barang_nk`
--

CREATE TABLE `tbpo_barang_nk` (
  `id_brg_nk` int NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `kd_br_adm` varchar(25) NOT NULL,
  `kat_barang` varchar(25) NOT NULL,
  `nama_barang` text NOT NULL,
  `descnk` text NOT NULL,
  `satuan` int NOT NULL,
  `minimum_stock` decimal(18,2) NOT NULL DEFAULT '0.00',
  `gbr_barang` text NOT NULL,
  `qrcode_path` text NOT NULL,
  `qrcode_data` varchar(50) NOT NULL,
  `inputer` varchar(25) NOT NULL,
  `create_at` datetime NOT NULL,
  `last_updated` varchar(25) NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `kd_lokasi` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_barang_nk_lokasi`
--

CREATE TABLE `tbpo_barang_nk_lokasi` (
  `id_lokasi` int NOT NULL,
  `nama_lokasi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_barang_packaging`
--

CREATE TABLE `tbpo_barang_packaging` (
  `id_packaging` int NOT NULL,
  `id_barang` int NOT NULL,
  `isi_kemasan` decimal(20,6) DEFAULT NULL,
  `satuan_kemasan` varchar(20) DEFAULT NULL,
  `isi_per_dos` decimal(20,6) DEFAULT NULL,
  `isi_per_inner` decimal(20,6) DEFAULT NULL,
  `inner_per_dos` decimal(20,6) DEFAULT NULL,
  `satuan_dasar` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_detail_po`
--

CREATE TABLE `tbpo_detail_po` (
  `id_det_po` int NOT NULL,
  `kd_po` varchar(255) NOT NULL,
  `no_po` varchar(255) NOT NULL,
  `tgl_transaksi` date NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `nama_barang` text NOT NULL,
  `satuan` text NOT NULL,
  `qty` double NOT NULL,
  `isi` decimal(15,2) DEFAULT '0.00',
  `kemasan` decimal(15,2) DEFAULT '0.00',
  `qty_kecil` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `hrg_satuan` double NOT NULL,
  `harga_satuan_exclude` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `harga_satuan_kecil` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `harga_satuan_kecil_exclude` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `hrg_diskon` double NOT NULL DEFAULT '0',
  `hrg_total` double NOT NULL,
  `hrg_total_diskon` double NOT NULL DEFAULT '0',
  `id_diskon_merk` int DEFAULT NULL,
  `satuan_diskon` varchar(10) DEFAULT NULL,
  `nominal_diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `diskon_satuan_kecil` decimal(15,2) NOT NULL DEFAULT '0.00',
  `harga_satuan_kecil_setelah_diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_harga_setelah_diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_bonus` tinyint(1) NOT NULL DEFAULT '0',
  `keterangan_bonus` varchar(255) NOT NULL DEFAULT '',
  `keterangan_harga_ppn` varchar(20) NOT NULL DEFAULT '',
  `kd_user` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_detail_po_nk`
--

CREATE TABLE `tbpo_detail_po_nk` (
  `id_det_po_nk` int NOT NULL,
  `kd_po_nk` varchar(25) NOT NULL,
  `kd_po_req` varchar(25) NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `kd_bsys` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `nama_barang` text NOT NULL,
  `deskripsi` text NOT NULL,
  `keterangan` text NOT NULL,
  `qty` int NOT NULL,
  `satuan` int NOT NULL,
  `kat_barang` varchar(25) NOT NULL,
  `hrg_satuan` int NOT NULL,
  `hrg_nyata` int NOT NULL,
  `total_harga` int NOT NULL,
  `total_nyata` int NOT NULL,
  `gbr_produk` varchar(255) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_detail_req`
--

CREATE TABLE `tbpo_detail_req` (
  `id_det_po_nk` int NOT NULL,
  `kd_po_nk` varchar(25) NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `kd_bsys` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `nama_barang` text NOT NULL,
  `deskripsi` text NOT NULL,
  `keterangan` text NOT NULL,
  `qty` int NOT NULL,
  `satuan` int NOT NULL,
  `kat_barang` varchar(25) NOT NULL,
  `status` int NOT NULL,
  `sts_done` int NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_diskon`
--

CREATE TABLE `tbpo_diskon` (
  `id_diskon` int NOT NULL,
  `kd_po` varchar(255) NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nominal` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_diskon_merk`
--

CREATE TABLE `tbpo_diskon_merk` (
  `id_diskon` int NOT NULL,
  `no_po` varchar(50) DEFAULT NULL,
  `merk_barang` varchar(255) NOT NULL,
  `satuan_diskon` enum('BOX','PCS','LTR','KG') NOT NULL,
  `nominal_diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_file_bukti_beli`
--

CREATE TABLE `tbpo_file_bukti_beli` (
  `id_fk_bukti` int NOT NULL,
  `kd_po_nk` varchar(255) NOT NULL,
  `keterangan` text NOT NULL,
  `user_upload` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_uploaded` varchar(255) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_file_nk`
--

CREATE TABLE `tbpo_file_nk` (
  `id_file_nk` int NOT NULL,
  `kd_po_nk` varchar(255) NOT NULL,
  `keterangan` text NOT NULL,
  `user_upload` varchar(25) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_uploaded` varchar(255) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_formula`
--

CREATE TABLE `tbpo_formula` (
  `id_formula` int NOT NULL,
  `kode_formula` varchar(50) NOT NULL,
  `nama_formula` varchar(150) NOT NULL,
  `deskripsi` text,
  `formula_expression` text NOT NULL,
  `output_label` varchar(100) NOT NULL,
  `output_unit` varchar(50) DEFAULT NULL,
  `rounding_mode` enum('none','round','ceil','floor') DEFAULT 'none',
  `decimal_place` int DEFAULT '2',
  `status` tinyint DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_formula_result`
--

CREATE TABLE `tbpo_formula_result` (
  `id_result` int NOT NULL,
  `id_po_detail` int DEFAULT NULL,
  `id_formula` int NOT NULL,
  `input_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `formula_expression` text NOT NULL,
  `result_value` decimal(20,6) NOT NULL,
  `result_label` varchar(100) DEFAULT NULL,
  `result_unit` varchar(50) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_formula_variable`
--

CREATE TABLE `tbpo_formula_variable` (
  `id_variable` int NOT NULL,
  `id_formula` int NOT NULL,
  `variable_key` varchar(100) NOT NULL,
  `variable_label` varchar(150) NOT NULL,
  `input_type` enum('number','decimal','currency') DEFAULT 'decimal',
  `unit` varchar(50) DEFAULT NULL,
  `default_value` decimal(20,6) DEFAULT NULL,
  `is_required` tinyint DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_generateqrcode`
--

CREATE TABLE `tbpo_generateqrcode` (
  `id_gqrcode` int NOT NULL,
  `kd_qrcode` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_generate_kd`
--

CREATE TABLE `tbpo_generate_kd` (
  `id` int NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_generate_kd_ponk`
--

CREATE TABLE `tbpo_generate_kd_ponk` (
  `id` int NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_kat_br`
--

CREATE TABLE `tbpo_kat_br` (
  `id_kat_br` int NOT NULL,
  `kd_kat` varchar(25) NOT NULL,
  `nama_kategori` text NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_notetemplate`
--

CREATE TABLE `tbpo_notetemplate` (
  `id_nt_template` int NOT NULL,
  `kd_nt_template` varchar(255) NOT NULL,
  `nama_note` text NOT NULL,
  `shipment_to` text NOT NULL,
  `alamat_ship` text NOT NULL,
  `cp_shipment` text NOT NULL,
  `no_cp` text NOT NULL,
  `ket_1` text NOT NULL,
  `ket_2` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_note_barang`
--

CREATE TABLE `tbpo_note_barang` (
  `id_nt_barang` int NOT NULL,
  `kd_po` varchar(25) NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `isi_note` text NOT NULL,
  `color_box` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_note_direktur`
--

CREATE TABLE `tbpo_note_direktur` (
  `id_note` int NOT NULL,
  `kd_po` varchar(25) NOT NULL,
  `isi_note` text NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `nama_user` text NOT NULL,
  `note_for` int NOT NULL,
  `update_status` int NOT NULL,
  `create_at` text NOT NULL,
  `log_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_note_pembelian`
--

CREATE TABLE `tbpo_note_pembelian` (
  `id_nt_pembelian` int NOT NULL,
  `kd_po` varchar(25) NOT NULL,
  `keterangan` text NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_nt_tmp_pembelian`
--

CREATE TABLE `tbpo_nt_tmp_pembelian` (
  `id_tmp_nt_pembelian` int NOT NULL,
  `keterangan` text NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_po`
--

CREATE TABLE `tbpo_po` (
  `id_po` int NOT NULL,
  `kd_po` varchar(255) NOT NULL,
  `no_po` varchar(255) NOT NULL,
  `tgl_transaksi` date NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `jml_item` int NOT NULL,
  `total_harga` double NOT NULL,
  `total_harga_diskon` double NOT NULL DEFAULT '0',
  `keterangan_harga_ppn` varchar(20) NOT NULL DEFAULT '',
  `total_harga_include` double NOT NULL DEFAULT '0',
  `total_harga_exlude` double NOT NULL DEFAULT '0',
  `total_harga_diskon_include` double NOT NULL DEFAULT '0',
  `total_harga_diskon_exlude` double NOT NULL DEFAULT '0',
  `status` varchar(255) NOT NULL,
  `tax` int NOT NULL,
  `hrg_pajak` double NOT NULL,
  `tmpo_pembayaran` int NOT NULL,
  `gdg_pengiriman` varchar(255) NOT NULL,
  `acc_with` varchar(25) NOT NULL,
  `kd_printout_note` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_po_nk`
--

CREATE TABLE `tbpo_po_nk` (
  `id_po_nk` int NOT NULL,
  `jns_po` int NOT NULL,
  `kd_po_nk` varchar(25) NOT NULL,
  `kd_po_req` varchar(25) NOT NULL,
  `nopo` text NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `nm_user` text NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `jml_item` int NOT NULL,
  `total_harga` int NOT NULL,
  `status` varchar(25) NOT NULL,
  `departemen` text NOT NULL,
  `tj_pembelian` text NOT NULL,
  `tax` int NOT NULL,
  `hrg_pajak` int NOT NULL,
  `hrg_nyata` int NOT NULL,
  `status_hrg_nyata` int NOT NULL,
  `acc_with` varchar(25) NOT NULL,
  `acc_with_kadep` varchar(255) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_ratings`
--

CREATE TABLE `tbpo_ratings` (
  `id_rating` int NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `kdq_rate` varchar(25) NOT NULL,
  `u_rate` int NOT NULL,
  `inpt_by` varchar(25) NOT NULL,
  `create_at` datetime NOT NULL,
  `update_by` varchar(25) NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_req_masterbarang`
--

CREATE TABLE `tbpo_req_masterbarang` (
  `id_reqmbarang` int NOT NULL,
  `nama_barang` text NOT NULL,
  `deskripsi` text NOT NULL,
  `satuan` int NOT NULL,
  `req_by` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_req_nk`
--

CREATE TABLE `tbpo_req_nk` (
  `id_po_nk` int NOT NULL,
  `jns_po` int NOT NULL,
  `kd_po_nk` varchar(25) NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `nm_user` text NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `tgl_ambil` text NOT NULL,
  `jml_item` int NOT NULL,
  `status` varchar(25) NOT NULL,
  `departemen` text NOT NULL,
  `tj_pembelian` text NOT NULL,
  `acc_with` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_satuan`
--

CREATE TABLE `tbpo_satuan` (
  `id_satuan` int NOT NULL,
  `nm_satuan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_set_note`
--

CREATE TABLE `tbpo_set_note` (
  `id_set_note` int NOT NULL,
  `note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_set_tax`
--

CREATE TABLE `tbpo_set_tax` (
  `id_tax` int NOT NULL,
  `nm_tax` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_sosialisasi`
--

CREATE TABLE `tbpo_sosialisasi` (
  `id` int NOT NULL,
  `module` int NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `status_nkomersil` int NOT NULL,
  `create_at` datetime NOT NULL,
  `done_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_suplier`
--

CREATE TABLE `tbpo_suplier` (
  `id_suplier` int NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `nama_suplier` text NOT NULL,
  `alamat_suplier` text NOT NULL,
  `no_telpon` text NOT NULL,
  `no_fax` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `gbr_logo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_tmp_diskon`
--

CREATE TABLE `tbpo_tmp_diskon` (
  `id_tmp_diskon` int NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `nama_diskon` text NOT NULL,
  `nominal` double NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_tmp_item`
--

CREATE TABLE `tbpo_tmp_item` (
  `id_tmp` int NOT NULL,
  `kode_barang` varchar(255) NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `nama_barang` text NOT NULL,
  `kode_suplier` varchar(255) NOT NULL,
  `satuan` varchar(255) NOT NULL,
  `qty` double NOT NULL,
  `isi` decimal(15,2) DEFAULT '0.00',
  `kemasan` decimal(15,2) DEFAULT '0.00',
  `qty_kecil` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `harga_satuan` double NOT NULL,
  `harga_satuan_exclude` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `harga_satuan_kecil` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `harga_satuan_kecil_exclude` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total_harga` double NOT NULL,
  `is_bonus` varchar(50) NOT NULL DEFAULT '0',
  `keterangan_bonus` varchar(255) NOT NULL DEFAULT '',
  `keterangan_harga_ppn` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_tmp_item_nk`
--

CREATE TABLE `tbpo_tmp_item_nk` (
  `id_tmp_nk` int NOT NULL,
  `jnis_po` int NOT NULL,
  `nama_barang` text NOT NULL,
  `deskripsi` text NOT NULL,
  `keterangan` text NOT NULL,
  `qty` int NOT NULL,
  `satuan` int NOT NULL,
  `hrg_satuan` int NOT NULL,
  `total_harga` int NOT NULL,
  `kd_bsys` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `kat_barang` varchar(25) NOT NULL,
  `kd_user` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_tmp_note_barang`
--

CREATE TABLE `tbpo_tmp_note_barang` (
  `id_nt_tmp_barang` int NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `isi_note` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_tmp_tax`
--

CREATE TABLE `tbpo_tmp_tax` (
  `id_tmp_tax` int NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `tax` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_tracking_po`
--

CREATE TABLE `tbpo_tracking_po` (
  `id_po_tracking` int NOT NULL,
  `kd_po` varchar(255) NOT NULL,
  `status` text NOT NULL,
  `createat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_transaksi`
--

CREATE TABLE `tbpo_transaksi` (
  `id_transnk` int NOT NULL,
  `kd_akun` varchar(25) NOT NULL,
  `kd_po_nk` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `kd_barangsys` varchar(25) NOT NULL,
  `keterangan` text NOT NULL,
  `kat_barang` varchar(25) NOT NULL,
  `tr_qty` double NOT NULL,
  `satuan` int NOT NULL,
  `inputer` varchar(25) NOT NULL,
  `req_by` varchar(25) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `create_at` date NOT NULL,
  `last_updated_by` varchar(25) NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_transaksi_tmp`
--

CREATE TABLE `tbpo_transaksi_tmp` (
  `id_transnk` int NOT NULL,
  `kd_akun` varchar(25) NOT NULL,
  `kd_po_nk` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `kd_barangsys` varchar(25) NOT NULL,
  `kat_barang` varchar(25) NOT NULL,
  `tr_qty` double NOT NULL,
  `satuan` int NOT NULL,
  `hrg_satuan` int NOT NULL,
  `status` varchar(255) NOT NULL,
  `keterangan` text NOT NULL,
  `inputer` varchar(25) NOT NULL,
  `create_at` datetime NOT NULL,
  `last_updated_by` varchar(25) NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_transaksi_trashbin`
--

CREATE TABLE `tbpo_transaksi_trashbin` (
  `id_trashbin` int NOT NULL,
  `kd_akun` varchar(25) NOT NULL,
  `kd_po_nk` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `kd_barangsys` varchar(25) NOT NULL,
  `keterangan` text NOT NULL,
  `kat_barang` varchar(25) NOT NULL,
  `tr_qty` double NOT NULL,
  `satuan` int NOT NULL,
  `inputer` varchar(25) NOT NULL,
  `req_by` varchar(25) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `create_at` date NOT NULL,
  `last_updated_by` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbpo_user`
--

CREATE TABLE `tbpo_user` (
  `id_user` int NOT NULL,
  `kode_user` varchar(25) NOT NULL,
  `nama_user` text NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `aksess_lv` int NOT NULL,
  `departement` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbq_module`
--

CREATE TABLE `tbq_module` (
  `id_qmodule` int NOT NULL,
  `kd_module` varchar(25) NOT NULL,
  `type_m` int NOT NULL,
  `nm_module` text NOT NULL,
  `m_status` int NOT NULL,
  `create_at` datetime NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastupdated` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbq_nilaim`
--

CREATE TABLE `tbq_nilaim` (
  `id` int NOT NULL,
  `kd_reviewq` varchar(25) NOT NULL,
  `kd_module` varchar(25) NOT NULL,
  `nilai` int NOT NULL,
  `inputer` varchar(25) NOT NULL,
  `create_at` datetime NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbq_review_pic`
--

CREATE TABLE `tbq_review_pic` (
  `id_review` int NOT NULL,
  `kd_module` varchar(25) NOT NULL,
  `kd_reviewq` varchar(25) NOT NULL,
  `isi_review` text NOT NULL,
  `nilai` int NOT NULL,
  `inputer` varchar(25) NOT NULL,
  `create_at` datetime NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbq_review_q`
--

CREATE TABLE `tbq_review_q` (
  `id_reviewq` int NOT NULL,
  `kd_reviewq` varchar(25) NOT NULL,
  `kd_module` varchar(25) NOT NULL,
  `question` text NOT NULL,
  `inputer` varchar(25) NOT NULL,
  `create_at` datetime NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbrp_activity_log`
--

CREATE TABLE `tbrp_activity_log` (
  `id_log` int UNSIGNED NOT NULL,
  `no_referensi` varchar(50) NOT NULL,
  `tipe_referensi` enum('spr','retur') NOT NULL,
  `aksi` varchar(50) NOT NULL,
  `status_awal` varchar(50) DEFAULT NULL,
  `status_akhir` varchar(50) DEFAULT NULL,
  `catatan` text,
  `user_by` varchar(150) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbrp_retur_penjualan_detail`
--

CREATE TABLE `tbrp_retur_penjualan_detail` (
  `id_retur_detail` int UNSIGNED NOT NULL,
  `id_retur` int UNSIGNED NOT NULL,
  `id_spr_detail` int UNSIGNED DEFAULT NULL,
  `no_urut` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `nama_barang` varchar(250) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `no_faktur` varchar(80) DEFAULT NULL,
  `no_batch` varchar(80) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `qty_retur` decimal(12,3) NOT NULL DEFAULT '0.000',
  `harga_satuan` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbrp_retur_penjualan_header`
--

CREATE TABLE `tbrp_retur_penjualan_header` (
  `id_retur` int UNSIGNED NOT NULL,
  `no_retur` varchar(40) NOT NULL,
  `tipe_retur` enum('biasa','replace','service') NOT NULL DEFAULT 'biasa',
  `total_nilai_retur` decimal(15,2) NOT NULL DEFAULT '0.00',
  `sisa_saldo_retur` decimal(15,2) NOT NULL DEFAULT '0.00',
  `id_spr` int UNSIGNED NOT NULL,
  `no_spr` varchar(30) NOT NULL,
  `gudang_id` int DEFAULT NULL,
  `tanggal_retur` date NOT NULL,
  `kd_customer` varchar(30) DEFAULT NULL,
  `nama_customer` varchar(200) DEFAULT NULL,
  `alamat` varchar(300) DEFAULT NULL,
  `nama_sales` varchar(150) DEFAULT NULL,
  `catatan_logistik` text,
  `status_retur` enum('menunggu_verifikasi','retur_menunggu_kadepub','retur_menunggu_mngacc','retur_menunggu_mngsc','retur_menunggu_mngse','retur_menunggu_kadepsc','retur_menunggu_dirop','retur_menunggu_dirut','menunggu_collection','menunggu_kasir','selesai','ditolak') NOT NULL DEFAULT 'menunggu_verifikasi',
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
  `catatan_admin_stock` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbrp_spr_detail`
--

CREATE TABLE `tbrp_spr_detail` (
  `id_spr_detail` int UNSIGNED NOT NULL,
  `id_spr` int UNSIGNED NOT NULL,
  `no_urut` tinyint UNSIGNED NOT NULL DEFAULT '1',
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
  `alasan_lainlain` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbrp_spr_header`
--

CREATE TABLE `tbrp_spr_header` (
  `id_spr` int UNSIGNED NOT NULL,
  `no_spr` varchar(30) NOT NULL,
  `tipe_retur` enum('biasa','replace','service') NOT NULL DEFAULT 'biasa',
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
  `koor_sc_catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbsim_bobotkpi`
--

CREATE TABLE `tbsim_bobotkpi` (
  `idbobotkpi` int NOT NULL,
  `id_user` int NOT NULL,
  `bobotwhat` int NOT NULL,
  `bobothow` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbsim_hows`
--

CREATE TABLE `tbsim_hows` (
  `id_how` int NOT NULL,
  `id_user` int NOT NULL,
  `id_kpi` int NOT NULL,
  `tipe_how` enum('A','B') DEFAULT 'A',
  `p_how` text NOT NULL,
  `bobot` double NOT NULL,
  `target_omset` decimal(15,2) DEFAULT '0.00',
  `hasil` text NOT NULL,
  `nilai` double NOT NULL,
  `total` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbsim_indikator_hows`
--

CREATE TABLE `tbsim_indikator_hows` (
  `id_indikator` int NOT NULL,
  `id_how` int NOT NULL,
  `keterangan` text NOT NULL,
  `nilai` decimal(5,2) NOT NULL,
  `urutan` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbsim_indikator_whats`
--

CREATE TABLE `tbsim_indikator_whats` (
  `id_indikator` int NOT NULL,
  `id_what` int NOT NULL,
  `keterangan` text NOT NULL,
  `nilai` decimal(5,2) NOT NULL,
  `urutan` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbsim_kpi`
--

CREATE TABLE `tbsim_kpi` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `poin` text NOT NULL,
  `bobot` double NOT NULL,
  `poin2` text NOT NULL,
  `bobot2` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbsim_whats`
--

CREATE TABLE `tbsim_whats` (
  `id_what` int NOT NULL,
  `id_user` int NOT NULL,
  `id_kpi` int NOT NULL,
  `tipe_what` enum('A','B') DEFAULT 'A',
  `p_what` text NOT NULL,
  `bobot` double NOT NULL,
  `target_omset` decimal(15,2) DEFAULT '0.00',
  `hasil` text NOT NULL,
  `nilai` double NOT NULL,
  `total` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_activity_log`
--

CREATE TABLE `tbso_activity_log` (
  `id` int NOT NULL,
  `no_so` varchar(50) DEFAULT NULL,
  `no_faktur` varchar(30) DEFAULT NULL,
  `aksi` varchar(50) NOT NULL,
  `keterangan` text,
  `detail_produk` text,
  `dilakukan_oleh` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_approval_harga`
--

CREATE TABLE `tbso_approval_harga` (
  `id` int NOT NULL,
  `id_so` int NOT NULL,
  `id_so_detail` int NOT NULL,
  `harga_lama` decimal(15,2) NOT NULL,
  `harga_baru` decimal(15,2) NOT NULL,
  `requested_by` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `approved_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_cancel_partial_request`
--

CREATE TABLE `tbso_cancel_partial_request` (
  `id` int NOT NULL,
  `id_so` int NOT NULL,
  `id_so_detail` int NOT NULL,
  `qty_cancel` decimal(15,3) NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `request_by` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `request_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approve_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approve_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_faktur_detail`
--

CREATE TABLE `tbso_faktur_detail` (
  `id` int NOT NULL,
  `id_faktur` int NOT NULL COMMENT 'FK ke tbso_faktur_penjualan',
  `no_faktur` varchar(30) NOT NULL,
  `id_so` int NOT NULL COMMENT 'FK ke tbso_sales_order',
  `id_so_detail` int NOT NULL COMMENT 'FK ke tbso_sales_order_detail.id',
  `kd_barang` varchar(50) NOT NULL,
  `nama_barang` varchar(200) DEFAULT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `qty` decimal(15,3) NOT NULL COMMENT 'Qty yg difakturkan (pcs)',
  `qty_box` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_satuan` decimal(15,3) NOT NULL DEFAULT '0.000',
  `isi_per_box` int NOT NULL DEFAULT '1',
  `satuan` varchar(20) DEFAULT NULL,
  `hrg_satuan` decimal(18,2) NOT NULL DEFAULT '0.00',
  `hrg_pokok` decimal(18,2) NOT NULL DEFAULT '0.00',
  `disc` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pajak` decimal(5,2) NOT NULL DEFAULT '0.00',
  `subtotal_before_disc` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal_after_disc` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_harga` decimal(18,2) NOT NULL DEFAULT '0.00',
  `berat_gram` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `kubikasi_m3` decimal(12,6) NOT NULL DEFAULT '0.000000',
  `gudang_id` varchar(30) DEFAULT NULL,
  `create_by` varchar(50) DEFAULT NULL,
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Detail Baris Faktur Penjualan';

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_faktur_jurnal`
--

CREATE TABLE `tbso_faktur_jurnal` (
  `id` int NOT NULL,
  `id_faktur` int NOT NULL,
  `no_faktur` varchar(30) NOT NULL,
  `piutang_dagang` decimal(15,2) NOT NULL DEFAULT '0.00',
  `penjualan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `ppn_keluar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_faktur_log`
--

CREATE TABLE `tbso_faktur_log` (
  `id` int NOT NULL,
  `no_so` varchar(50) DEFAULT NULL,
  `no_faktur` varchar(50) DEFAULT NULL,
  `id_faktur` int DEFAULT NULL,
  `aksi` varchar(50) NOT NULL,
  `keterangan` text,
  `detail_produk` text,
  `dilakukan_oleh` varchar(100) NOT NULL DEFAULT 'system',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_faktur_penjualan`
--

CREATE TABLE `tbso_faktur_penjualan` (
  `id_faktur` int NOT NULL,
  `no_faktur` varchar(30) NOT NULL COMMENT 'e.g. INV/202506/0001',
  `id_so` int NOT NULL COMMENT 'FK ke tbso_sales_order.id_so',
  `no_so` varchar(30) NOT NULL,
  `kd_customer` varchar(50) NOT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `gudang_id` varchar(30) DEFAULT NULL,
  `tanggal_faktur` date NOT NULL,
  `total_tonase` decimal(15,3) NOT NULL DEFAULT '0.000',
  `total_kubikasi` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `catatan` text,
  `status` varchar(20) NOT NULL DEFAULT 'confirmed' COMMENT 'confirmed | cancelled',
  `create_by` varchar(50) DEFAULT NULL,
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_by` varchar(50) DEFAULT NULL,
  `update_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `salesman` varchar(100) DEFAULT NULL,
  `cara_pembayaran` varchar(20) DEFAULT 'cash',
  `jtempo` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `tempo` int NOT NULL DEFAULT '0',
  `parent_id_faktur` int DEFAULT NULL,
  `is_split_parent` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Header Faktur Penjualan dari Sales Order';

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_sales_order`
--

CREATE TABLE `tbso_sales_order` (
  `id_so` int NOT NULL,
  `no_so` varchar(30) DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `kd_customer` varchar(50) DEFAULT NULL,
  `kd_rute` varchar(50) DEFAULT NULL,
  `loading_tgl_pengiriman` date DEFAULT NULL,
  `loading_jenis_pengiriman` varchar(30) NOT NULL DEFAULT 'expedisi_kantor',
  `loading_driver` varchar(100) DEFAULT NULL,
  `loading_nolambung` varchar(100) DEFAULT NULL,
  `loading_urutan` int NOT NULL DEFAULT '0',
  `customer_name` varchar(150) NOT NULL,
  `gudang_id` varchar(30) NOT NULL,
  `jumlah_item` int NOT NULL DEFAULT '0',
  `total_tonase` decimal(15,3) NOT NULL DEFAULT '0.000',
  `total_kubikasi` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `batas_tonase` decimal(15,3) DEFAULT NULL,
  `batas_kubikasi` decimal(15,5) DEFAULT NULL,
  `status` enum('draft','waiting_approval','approved','open','sedang_verifikasi','siap_faktur','partial','partial_delivered','in_delivery','completed','cancelled') NOT NULL DEFAULT 'draft',
  `catatan` text,
  `cara_pembayaran` varchar(20) DEFAULT 'cash',
  `is_faktur_z` tinyint(1) NOT NULL DEFAULT '0',
  `create_by` varchar(50) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_by` varchar(50) DEFAULT NULL,
  `update_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `approve_by` varchar(100) DEFAULT NULL,
  `no_faktur` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_sales_order_detail`
--

CREATE TABLE `tbso_sales_order_detail` (
  `id` int NOT NULL,
  `id_so` int DEFAULT NULL COMMENT 'FK ke tbso_sales_order.id_so',
  `no_so` varchar(30) NOT NULL,
  `produk_id` varchar(50) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nama_barang` varchar(200) NOT NULL,
  `qty` decimal(15,3) NOT NULL,
  `qty_box` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_satuan` decimal(15,3) NOT NULL DEFAULT '0.000',
  `isi_per_box` int NOT NULL DEFAULT '1',
  `satuan` varchar(20) NOT NULL,
  `expired_date` date NOT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `pajak` decimal(5,2) NOT NULL DEFAULT '0.00',
  `disc` decimal(5,2) NOT NULL DEFAULT '0.00',
  `subtotal_before_disc` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal_after_disc` decimal(15,2) NOT NULL DEFAULT '0.00',
  `hrg_satuan` decimal(18,2) NOT NULL,
  `hrg_pokok` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_harga` decimal(18,2) NOT NULL,
  `berat_gram` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `kubikasi_m3` decimal(12,6) NOT NULL DEFAULT '0.000000',
  `kode_akun` varchar(20) DEFAULT NULL,
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_by` varchar(50) NOT NULL,
  `qty_faktur` int DEFAULT '0',
  `qty_siap_faktur` decimal(12,3) DEFAULT NULL,
  `qty_tidak_terkirim` decimal(12,3) NOT NULL DEFAULT '0.000',
  `verifikasi_loading_status` varchar(20) NOT NULL DEFAULT 'pending',
  `verifikasi_loading_note` text,
  `verifikasi_loading_by` varchar(50) DEFAULT NULL,
  `verifikasi_loading_at` datetime DEFAULT NULL,
  `checker_loaded` tinyint(1) NOT NULL DEFAULT '0',
  `qty_outstanding` int DEFAULT '0',
  `approve_by` varchar(100) DEFAULT NULL,
  `is_nego` tinyint(1) NOT NULL DEFAULT '0',
  `no_faktur` varchar(30) DEFAULT NULL,
  `qty_delivered` decimal(15,3) NOT NULL DEFAULT '0.000',
  `ref_no` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_so_approval`
--

CREATE TABLE `tbso_so_approval` (
  `id` bigint UNSIGNED NOT NULL,
  `no_faktur` varchar(30) NOT NULL,
  `no_so` varchar(30) DEFAULT NULL,
  `tipe` varchar(30) NOT NULL DEFAULT 'harga',
  `keterangan` varchar(500) DEFAULT NULL,
  `req_by` varchar(100) DEFAULT NULL,
  `approve_by` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `req_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` varchar(500) DEFAULT NULL,
  `act_by` varchar(100) DEFAULT NULL,
  `act_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbso_stock_reservation`
--

CREATE TABLE `tbso_stock_reservation` (
  `id` bigint UNSIGNED NOT NULL,
  `no_faktur` varchar(30) NOT NULL,
  `no_so` varchar(30) DEFAULT NULL,
  `id_so_detail` int DEFAULT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `exp_date` varchar(10) DEFAULT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `gudang_id` varchar(30) NOT NULL,
  `qty_reserved` decimal(15,3) NOT NULL DEFAULT '0.000',
  `status` enum('active','released') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_akses_level`
--

CREATE TABLE `tb_akses_level` (
  `id` int NOT NULL,
  `nama_level` varchar(100) NOT NULL,
  `kode_level` varchar(50) NOT NULL,
  `deskripsi` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_akses_menu`
--

CREATE TABLE `tb_akses_menu` (
  `id` bigint NOT NULL,
  `akses_lv_id` int DEFAULT NULL,
  `id_menu` int NOT NULL DEFAULT '0',
  `menu_id` int DEFAULT NULL,
  `can_view` tinyint(1) DEFAULT '0',
  `can_add` tinyint(1) DEFAULT '0',
  `can_edit` tinyint(1) DEFAULT '0',
  `can_delete` tinyint(1) DEFAULT '0',
  `can_approve` tinyint(1) DEFAULT '0',
  `can_print` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_auth`
--

CREATE TABLE `tb_auth` (
  `id_auth` int NOT NULL,
  `id_user` int NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_auth_backup_20260610_072706`
--

CREATE TABLE `tb_auth_backup_20260610_072706` (
  `id_auth` int NOT NULL DEFAULT '0',
  `id_user` int NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_barangv2`
--

CREATE TABLE `tb_barangv2` (
  `id_barang` int NOT NULL,
  `kode_barang` varchar(30) NOT NULL,
  `nama_barang` text NOT NULL,
  `nama_suplier` text NOT NULL,
  `produk_fokus` varchar(5) NOT NULL,
  `kelompok` text NOT NULL,
  `bahan_aktif` text NOT NULL,
  `gbr_produk` varchar(255) NOT NULL,
  `gbr_promo1` varchar(255) NOT NULL,
  `gbr_promo2` varchar(255) NOT NULL,
  `gbr_promo3` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_bobotkpi`
--

CREATE TABLE `tb_bobotkpi` (
  `idbobotkpi` int NOT NULL,
  `id_user` int NOT NULL,
  `bobotwhat` int NOT NULL,
  `bobothow` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_bongkaran`
--

CREATE TABLE `tb_bongkaran` (
  `id` int NOT NULL,
  `kode_bongkar` varchar(50) NOT NULL COMMENT 'Kode unik bongkaran',
  `keterangan` text COMMENT 'Deskripsi / info bongkaran',
  `pintu` tinyint(1) DEFAULT NULL,
  `status` enum('MENUNGGU','PROSES','PENYIAPAN_BARANG','CETAK_DO','DONE') NOT NULL DEFAULT 'MENUNGGU',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = sudah diarsipkan',
  `archived_at` datetime DEFAULT NULL,
  `archived_by` varchar(100) DEFAULT NULL,
  `created_by` varchar(100) NOT NULL COMMENT 'NIK atau nama Manager WH yang membuat',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Data bongkaran utama';

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_bongkaran_checker`
--

CREATE TABLE `tb_bongkaran_checker` (
  `id` int NOT NULL,
  `id_bongkaran` int NOT NULL,
  `nik_checker` varchar(25) NOT NULL,
  `nm_checker` varchar(150) NOT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `progres` int NOT NULL DEFAULT '0' COMMENT 'Persentase 0-100',
  `status_checker` enum('PROSES','DONE') NOT NULL DEFAULT 'PROSES',
  `is_paused` tinyint(1) NOT NULL DEFAULT '0',
  `total_pause_secs` int NOT NULL DEFAULT '0',
  `paused_at` datetime DEFAULT NULL,
  `pernah_pause` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Progres checker per bongkaran';

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_checklist_kendaraan`
--

CREATE TABLE `tb_checklist_kendaraan` (
  `id` int NOT NULL,
  `tanggal_check` date NOT NULL,
  `driver` varchar(100) DEFAULT NULL,
  `nopol` varchar(20) DEFAULT NULL,
  `no_lambung` varchar(50) DEFAULT NULL,
  `kilometer` int DEFAULT NULL,
  `inputer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_checklist_kendaraan_detail`
--

CREATE TABLE `tb_checklist_kendaraan_detail` (
  `id` int NOT NULL,
  `checklist_id` int NOT NULL,
  `nama_part` varchar(100) DEFAULT NULL,
  `kondisi` enum('BAIK','TIDAK BAIK') DEFAULT 'BAIK',
  `keterangan` varchar(255) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_checkup_mekanik_kategori`
--

CREATE TABLE `tb_checkup_mekanik_kategori` (
  `id_kategori` int NOT NULL,
  `nm_kategori` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_checkup_mekanik_kategori_detail`
--

CREATE TABLE `tb_checkup_mekanik_kategori_detail` (
  `id_detail_kat` int NOT NULL,
  `id_kategori` int NOT NULL,
  `nm_detail` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_checkup_mekanik_kategori_foto`
--

CREATE TABLE `tb_checkup_mekanik_kategori_foto` (
  `id` int NOT NULL,
  `id_ckup` int NOT NULL,
  `id_kategori` int NOT NULL,
  `foto` varchar(255) NOT NULL,
  `input_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_customer`
--

CREATE TABLE `tb_customer` (
  `id` int NOT NULL,
  `kd_customer` varchar(25) NOT NULL,
  `nama_customer` text,
  `nama_sales` varchar(100) DEFAULT NULL,
  `nama_kios` text,
  `plafon_aktif` decimal(16,2) DEFAULT NULL,
  `plafon_updated_at` datetime DEFAULT NULL,
  `alamat_kios` text,
  `telp1` text,
  `telp2` text,
  `regional` text,
  `kd_rute` varchar(100) DEFAULT NULL,
  `jam_buka_tutup` text NOT NULL,
  `karakteristik_kios` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_customer_list_undian`
--

CREATE TABLE `tb_customer_list_undian` (
  `id` int NOT NULL,
  `kat_undi` text NOT NULL,
  `noundi` int NOT NULL,
  `nama_customer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_dailystock`
--

CREATE TABLE `tb_dailystock` (
  `id` int NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `gudang` text NOT NULL,
  `qty` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_dailystock_global`
--

CREATE TABLE `tb_dailystock_global` (
  `id` int NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `gudang` text NOT NULL,
  `qty` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_departemen`
--

CREATE TABLE `tb_departemen` (
  `id` int NOT NULL,
  `nama_departemen` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_detail_do`
--

CREATE TABLE `tb_detail_do` (
  `id` int NOT NULL,
  `id_pre_do` int NOT NULL,
  `kd_do` varchar(25) NOT NULL,
  `kd_faktur` varchar(25) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `kd_rute` text NOT NULL,
  `kd_customer` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `nama_barang` text NOT NULL,
  `qty` int NOT NULL,
  `satuan` text NOT NULL,
  `no_lot` text NOT NULL,
  `tgl_exp` text NOT NULL,
  `norut` int NOT NULL,
  `nominal_p` double NOT NULL,
  `jtempo` int NOT NULL,
  `note_faktur` text NOT NULL,
  `dt_status` int NOT NULL,
  `status` int NOT NULL,
  `input_at` text NOT NULL,
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_detail_mutasi`
--

CREATE TABLE `tb_detail_mutasi` (
  `id` int NOT NULL,
  `noreff` varchar(255) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `gdg_asal` int NOT NULL,
  `gdg_mutasi` int NOT NULL,
  `kode_barang` varchar(255) NOT NULL,
  `kode_barang_zahir` varchar(50) NOT NULL,
  `nama_barang` text NOT NULL,
  `exp_date` text NOT NULL,
  `qty` int NOT NULL,
  `satuan` int NOT NULL,
  `input_by` text NOT NULL,
  `create_at` text NOT NULL,
  `last_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `no_lot` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_detail_retur_barang`
--

CREATE TABLE `tb_detail_retur_barang` (
  `id` int NOT NULL,
  `kd_retur` varchar(50) NOT NULL,
  `retur_type` int NOT NULL,
  `kd_faktur` varchar(50) NOT NULL,
  `kd_barang` varchar(30) NOT NULL,
  `no_lot` text NOT NULL,
  `tgl_expired` text NOT NULL,
  `qty` int NOT NULL,
  `status_data` int NOT NULL,
  `tgl_input` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_det_tracking_driver`
--

CREATE TABLE `tb_det_tracking_driver` (
  `id` int NOT NULL,
  `norut` int NOT NULL,
  `kd_deliveri` varchar(25) NOT NULL,
  `tgl_jalan` text NOT NULL,
  `kd_driver` varchar(25) NOT NULL,
  `kd_helper` varchar(25) NOT NULL,
  `kd_truk` varchar(25) NOT NULL,
  `destinasi` text NOT NULL,
  `jml_kios` int NOT NULL,
  `tonase` double NOT NULL,
  `kubikasi` double NOT NULL,
  `sts_driver` varchar(25) NOT NULL,
  `keterangan` text NOT NULL,
  `nm_toko` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_do`
--

CREATE TABLE `tb_do` (
  `id` int NOT NULL,
  `kd_do` varchar(25) NOT NULL,
  `nolambung` text NOT NULL,
  `regional` text NOT NULL,
  `driver` text NOT NULL,
  `tgl_pengiriman` date NOT NULL,
  `tgl_create` datetime NOT NULL,
  `status` int NOT NULL,
  `sales_confirm_at` datetime DEFAULT NULL,
  `sales_confirm_by` varchar(100) DEFAULT NULL,
  `sales_confirm_note` text,
  `sales_confirm_status` enum('pending','siap','belum_siap') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_editlog_faktur`
--

CREATE TABLE `tb_editlog_faktur` (
  `id` int NOT NULL,
  `kd_faktur` varchar(25) NOT NULL,
  `kd_customer` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `keterangan` text NOT NULL,
  `edit_by` text NOT NULL,
  `edit_at` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_eviden`
--

CREATE TABLE `tb_eviden` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `nama_eviden` varchar(255) NOT NULL,
  `namafoto` varchar(200) NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_eviden_backup`
--

CREATE TABLE `tb_eviden_backup` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `nama_eviden` varchar(255) NOT NULL,
  `namafoto` varchar(200) NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_expedisi`
--

CREATE TABLE `tb_expedisi` (
  `id` int NOT NULL,
  `tanggal` text NOT NULL,
  `jammasuk` varchar(255) NOT NULL,
  `jamkeluar` varchar(255) NOT NULL,
  `nopol` varchar(255) NOT NULL,
  `namadriver` varchar(255) NOT NULL,
  `notlpndriver` varchar(255) NOT NULL,
  `perusahaanpengirim` varchar(255) NOT NULL,
  `namabarang` varchar(255) NOT NULL,
  `jumlahbarang` varchar(255) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nm_inputer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_feedback`
--

CREATE TABLE `tb_feedback` (
  `id_feedback` int NOT NULL,
  `id_user_pemberi` int NOT NULL,
  `id_user_penerima` int NOT NULL,
  `feedback` text NOT NULL,
  `bulan` varchar(10) NOT NULL,
  `tanggal_buat` datetime NOT NULL,
  `tanggal_update` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_gbrupload_cheklist`
--

CREATE TABLE `tb_gbrupload_cheklist` (
  `id_upload` int NOT NULL,
  `id_cheklist` int NOT NULL,
  `name_file` text NOT NULL,
  `path` text NOT NULL,
  `tgl_create` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_gudang`
--

CREATE TABLE `tb_gudang` (
  `id_gudang` int NOT NULL,
  `nama_gudang` varchar(100) NOT NULL,
  `tipe` enum('INDUK','ECERAN','EXPIRED') NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_gudang_wilayah`
--

CREATE TABLE `tb_gudang_wilayah` (
  `id_wilayah` int NOT NULL,
  `id_gudang` int NOT NULL,
  `nama_wilayah` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_hows`
--

CREATE TABLE `tb_hows` (
  `id_how` int NOT NULL,
  `id_user` int NOT NULL,
  `id_kpi` int NOT NULL,
  `tipe_how` enum('A','B') DEFAULT 'A',
  `p_how` text NOT NULL,
  `bobot` double NOT NULL,
  `target_omset` decimal(15,2) DEFAULT '0.00',
  `hasil` text,
  `nilai` double NOT NULL,
  `total` double NOT NULL,
  `is_edited` tinyint(1) DEFAULT '0',
  `edited_by` int DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `original_p_how` text,
  `original_bobot` double DEFAULT NULL,
  `original_hasil` text,
  `original_nilai` double DEFAULT NULL,
  `original_total` double DEFAULT NULL,
  `original_target_omset` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_ics`
--

CREATE TABLE `tb_ics` (
  `id` int NOT NULL,
  `kd_system` varchar(35) NOT NULL,
  `nama_barang` text NOT NULL,
  `exp_date` text NOT NULL,
  `qty` int NOT NULL,
  `qty_box` int NOT NULL,
  `qty_pcs` int NOT NULL,
  `pic` varchar(2) NOT NULL,
  `input_at` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_ics_do`
--

CREATE TABLE `tb_ics_do` (
  `id` int NOT NULL,
  `kd_do` text NOT NULL,
  `kd_faktur` varchar(25) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `kd_barang` varchar(30) NOT NULL,
  `nama_barang` text NOT NULL,
  `qty` int NOT NULL,
  `no_lot` text NOT NULL,
  `exp_date` text NOT NULL,
  `input_at` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_ics_opname`
--

CREATE TABLE `tb_ics_opname` (
  `id` int NOT NULL,
  `kd_system` varchar(25) NOT NULL,
  `nama_barang` text NOT NULL,
  `exp_date` text NOT NULL,
  `qty` int NOT NULL,
  `qty_box` int NOT NULL,
  `qty_pcs` int NOT NULL,
  `inputer` text NOT NULL,
  `tim` text NOT NULL,
  `wilayah` text NOT NULL,
  `input_at` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_ics_po`
--

CREATE TABLE `tb_ics_po` (
  `id` int NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `kd_faktur_lpb` varchar(30) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nama_barang` text NOT NULL,
  `exp_date` text NOT NULL,
  `qty` int NOT NULL,
  `lpb_note` text NOT NULL,
  `input_at` text NOT NULL,
  `lpb_status` int NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_ics_supp`
--

CREATE TABLE `tb_ics_supp` (
  `id` int NOT NULL,
  `nama_barang` text NOT NULL,
  `qty` int NOT NULL,
  `no_lot` text NOT NULL,
  `exp_date` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_indikator_hows`
--

CREATE TABLE `tb_indikator_hows` (
  `id_indikator` int NOT NULL,
  `id_how` int NOT NULL,
  `keterangan` text NOT NULL,
  `nilai` decimal(5,2) NOT NULL,
  `urutan` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_edited` tinyint(1) DEFAULT '0',
  `edited_by` int DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `original_keterangan` text,
  `original_nilai` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_indikator_whats`
--

CREATE TABLE `tb_indikator_whats` (
  `id_indikator` int NOT NULL,
  `id_what` int NOT NULL,
  `keterangan` text NOT NULL,
  `nilai` decimal(5,2) NOT NULL,
  `urutan` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_edited` tinyint(1) DEFAULT '0',
  `edited_by` int DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `original_keterangan` text,
  `original_nilai` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_issue`
--

CREATE TABLE `tb_issue` (
  `id` int NOT NULL,
  `tanggal` text NOT NULL,
  `issue` varchar(255) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `status` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_jobdesk`
--

CREATE TABLE `tb_jobdesk` (
  `id` int NOT NULL,
  `nama_jobdesk` varchar(100) NOT NULL,
  `deskripsi` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_karyawan`
--

CREATE TABLE `tb_karyawan` (
  `id` int NOT NULL,
  `nik` varchar(25) DEFAULT NULL,
  `nm_karyawan` text,
  `departemen` varchar(20) DEFAULT NULL,
  `jobdesk` text,
  `username` varchar(255) DEFAULT NULL,
  `faktur_prefix` varchar(4) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `tim` int NOT NULL,
  `wilayah` int NOT NULL,
  `akses_lv` int DEFAULT NULL,
  `akses_lv_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `departemen_id` int DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `jobdesk_id` int DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `status_karyawan` enum('AKTIF','NONAKTIF') DEFAULT 'AKTIF',
  `tim_id` int DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `wilayah_id` varchar(225) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_karyawan_keluarmasuk`
--

CREATE TABLE `tb_karyawan_keluarmasuk` (
  `id` int NOT NULL,
  `tanggal` text NOT NULL,
  `nama` varchar(255) NOT NULL,
  `departemen` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `jamkeluar` varchar(255) NOT NULL,
  `jammasuk` varchar(255) NOT NULL,
  `nopol` text NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nm_inputer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kasbon`
--

CREATE TABLE `tb_kasbon` (
  `id` int NOT NULL,
  `no_kasbon` varchar(50) NOT NULL,
  `id_user` int NOT NULL,
  `nama_pemohon` varchar(100) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` text NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kd_system_stock`
--

CREATE TABLE `tb_kd_system_stock` (
  `id` int NOT NULL,
  `kd_system` varchar(25) NOT NULL,
  `nama_barang` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kpi`
--

CREATE TABLE `tb_kpi` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `poin` text NOT NULL,
  `bobot` double NOT NULL,
  `poin2` text NOT NULL,
  `bobot2` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kpi_history`
--

CREATE TABLE `tb_kpi_history` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `id_kpi` int DEFAULT NULL,
  `bulan` varchar(7) NOT NULL,
  `poin_what` text,
  `poin_how` text,
  `bobot_what` decimal(5,2) DEFAULT '0.00',
  `bobot_how` decimal(5,2) DEFAULT '0.00',
  `total_what_raw` decimal(10,2) DEFAULT '0.00',
  `total_how_raw` decimal(10,2) DEFAULT '0.00',
  `nilai_what` decimal(10,2) DEFAULT '0.00',
  `nilai_how` decimal(10,2) DEFAULT '0.00',
  `is_summary` tinyint(1) DEFAULT '0',
  `total_kpi_real` decimal(10,2) DEFAULT '0.00',
  `total_kpi_target` decimal(10,2) DEFAULT '0.00',
  `total_what` decimal(10,2) DEFAULT '0.00',
  `total_how` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kpi_history_backup_all_user_apr_mei_2026`
--

CREATE TABLE `tb_kpi_history_backup_all_user_apr_mei_2026` (
  `id` int NOT NULL DEFAULT '0',
  `id_user` int NOT NULL,
  `id_kpi` int DEFAULT NULL,
  `bulan` varchar(7) NOT NULL,
  `poin_what` text,
  `poin_how` text,
  `bobot_what` decimal(5,2) DEFAULT '0.00',
  `bobot_how` decimal(5,2) DEFAULT '0.00',
  `total_what_raw` decimal(10,2) DEFAULT '0.00',
  `total_how_raw` decimal(10,2) DEFAULT '0.00',
  `nilai_what` decimal(10,2) DEFAULT '0.00',
  `nilai_how` decimal(10,2) DEFAULT '0.00',
  `is_summary` tinyint(1) DEFAULT '0',
  `total_kpi_real` decimal(10,2) DEFAULT '0.00',
  `total_kpi_target` decimal(10,2) DEFAULT '0.00',
  `total_what` decimal(10,2) DEFAULT '0.00',
  `total_how` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kpi_lock_settings`
--

CREATE TABLE `tb_kpi_lock_settings` (
  `id_lock` int NOT NULL,
  `nama_periode` varchar(100) NOT NULL,
  `tipe_periode` enum('sekali','bulanan','tahunan') DEFAULT 'sekali',
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '0',
  `recurring_day_start` int DEFAULT NULL,
  `recurring_day_end` int DEFAULT NULL,
  `level_akses` varchar(50) NOT NULL COMMENT 'Format: 1,2,3,4 atau kosong jika semua ditutup',
  `izin_akses` text NOT NULL COMMENT 'Format JSON: {"view":true,"add":true,"edit":true,"delete":true}',
  `keterangan` text,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kpi_reset_log`
--

CREATE TABLE `tb_kpi_reset_log` (
  `id` int NOT NULL,
  `bulan` tinyint NOT NULL COMMENT '1-12',
  `tahun` smallint NOT NULL,
  `reset_by` int NOT NULL COMMENT 'id_user Admin HRD yang melakukan reset',
  `catatan` text COMMENT 'Alasan/catatan reset',
  `jumlah_tereset` int DEFAULT '0' COMMENT 'Jumlah row yang direset',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Log riwayat reset KPI oleh Admin HRD';

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kpi_verified`
--

CREATE TABLE `tb_kpi_verified` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `bulan` varchar(10) NOT NULL,
  `verified_by` int NOT NULL,
  `verified_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_lap_distribusi`
--

CREATE TABLE `tb_lap_distribusi` (
  `id` int NOT NULL,
  `nopol` varchar(255) NOT NULL,
  `nolambung` varchar(255) NOT NULL,
  `namadriver` varchar(255) NOT NULL,
  `namahelper` varchar(255) NOT NULL,
  `tujuan` text NOT NULL,
  `tglkeluar` text NOT NULL,
  `jamkeluar` varchar(255) NOT NULL,
  `kmkeluar` text NOT NULL,
  `tglmasuk` text NOT NULL,
  `jammasuk` varchar(255) NOT NULL,
  `kmmasuk` varchar(255) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `inputer` varchar(255) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_loading_kk`
--

CREATE TABLE `tb_loading_kk` (
  `id` int NOT NULL,
  `kode` varchar(20) DEFAULT NULL,
  `tgl` date NOT NULL DEFAULT (curdate()),
  `waktu_siap_loading` datetime DEFAULT NULL,
  `keterangan` varchar(200) NOT NULL COMMENT 'Contoh: JBR, JEMBER, dsb',
  `pintu` tinyint(1) DEFAULT NULL,
  `waktu_do_selesai` datetime DEFAULT NULL,
  `waktu_cetak_do` datetime DEFAULT NULL,
  `waktu_mulai_siapkan` datetime DEFAULT NULL,
  `waktu_selesai_siapkan` datetime DEFAULT NULL,
  `nik_checker` varchar(50) DEFAULT NULL,
  `nm_checker` varchar(100) DEFAULT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `progres` int NOT NULL DEFAULT '0',
  `progres_siapkan` int DEFAULT '0',
  `status` enum('MENUNGGU','SIAP_LOADING','CETAK_DO','DO_SELESAI','PENYIAPAN_BARANG','BARANG_SIAP','PROSES_LOADING','DONE') NOT NULL DEFAULT 'MENUNGGU',
  `is_paused` tinyint(1) NOT NULL DEFAULT '0',
  `total_pause_secs` int NOT NULL DEFAULT '0',
  `is_paused_siapkan` tinyint(1) DEFAULT '0',
  `paused_at_siapkan` datetime DEFAULT NULL,
  `total_pause_secs_siapkan` int DEFAULT '0',
  `pernah_pause_siapkan` tinyint(1) DEFAULT '0',
  `paused_at` datetime DEFAULT NULL,
  `pernah_pause` tinyint(1) NOT NULL DEFAULT '0',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `archived_at` datetime DEFAULT NULL,
  `archived_by` varchar(100) DEFAULT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Data loading jalur KK';

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_loading_lk`
--

CREATE TABLE `tb_loading_lk` (
  `id` int NOT NULL,
  `kode` varchar(20) DEFAULT NULL,
  `tgl` date NOT NULL DEFAULT (curdate()),
  `waktu_siap_loading` datetime DEFAULT NULL,
  `keterangan` varchar(200) NOT NULL COMMENT 'Contoh: P-2, dsb',
  `pintu` tinyint(1) DEFAULT NULL,
  `waktu_do_selesai` datetime DEFAULT NULL,
  `waktu_cetak_do` datetime DEFAULT NULL,
  `waktu_mulai_siapkan` datetime DEFAULT NULL,
  `waktu_selesai_siapkan` datetime DEFAULT NULL,
  `nik_checker` varchar(50) DEFAULT NULL,
  `nm_checker` varchar(100) DEFAULT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `progres` int NOT NULL DEFAULT '0',
  `progres_siapkan` int DEFAULT '0',
  `status` enum('MENUNGGU','SIAP_LOADING','CETAK_DO','DO_SELESAI','PENYIAPAN_BARANG','BARANG_SIAP','PROSES_LOADING','DONE') NOT NULL DEFAULT 'MENUNGGU',
  `is_paused` tinyint(1) NOT NULL DEFAULT '0',
  `is_paused_siapkan` tinyint(1) DEFAULT '0',
  `paused_at_siapkan` datetime DEFAULT NULL,
  `total_pause_secs_siapkan` int DEFAULT '0',
  `pernah_pause_siapkan` tinyint(1) DEFAULT '0',
  `total_pause_secs` int NOT NULL DEFAULT '0',
  `paused_at` datetime DEFAULT NULL,
  `pernah_pause` tinyint(1) NOT NULL DEFAULT '0',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `archived_at` datetime DEFAULT NULL,
  `archived_by` varchar(100) DEFAULT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_role` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Data loading jalur LK';

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_login_log`
--

CREATE TABLE `tb_login_log` (
  `id` bigint NOT NULL,
  `id_karyawan` int DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `browser` text,
  `platform` varchar(100) DEFAULT NULL,
  `status_login` enum('SUCCESS','FAILED') DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_log_confirm_sales`
--

CREATE TABLE `tb_log_confirm_sales` (
  `id` int NOT NULL,
  `kd_do` varchar(25) NOT NULL,
  `action` enum('siap','belum_siap') NOT NULL,
  `note` text,
  `confirm_by` varchar(100) DEFAULT NULL,
  `confirm_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_log_do`
--

CREATE TABLE `tb_log_do` (
  `id_log` int NOT NULL,
  `kd_do` text NOT NULL,
  `tgl_input` text NOT NULL,
  `keterangan` text NOT NULL,
  `inputer` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_log_ics`
--

CREATE TABLE `tb_log_ics` (
  `id` int NOT NULL,
  `nama_user` text NOT NULL,
  `nama_barang` text NOT NULL,
  `qty` int NOT NULL,
  `qty_box` int NOT NULL,
  `qty_pcs` int NOT NULL,
  `no_lot` text NOT NULL,
  `exp_date` text NOT NULL,
  `keterangan` text NOT NULL,
  `inputer` varchar(25) NOT NULL,
  `tgl_input` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_log_mutasi`
--

CREATE TABLE `tb_log_mutasi` (
  `id` bigint NOT NULL,
  `noreff` varchar(50) DEFAULT NULL,
  `aksi` varchar(50) DEFAULT NULL,
  `keterangan` text,
  `user` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_lpb`
--

CREATE TABLE `tb_lpb` (
  `id_lpb` int NOT NULL,
  `kd_po` varchar(100) DEFAULT NULL,
  `nosj` text NOT NULL,
  `tgl_sj` date NOT NULL,
  `no_po` varchar(100) DEFAULT NULL,
  `no_invoice` varchar(50) DEFAULT NULL,
  `gudang_id` int DEFAULT NULL,
  `keterangan` text,
  `input_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `checker_at` datetime DEFAULT NULL,
  `checker_by` varchar(50) DEFAULT NULL,
  `checker_name` varchar(100) DEFAULT NULL,
  `jenis_lpb` varchar(80) DEFAULT NULL,
  `kode_faktur_pajak` varchar(100) DEFAULT NULL,
  `nomor_lpb` varchar(30) DEFAULT NULL,
  `status_lpb` tinyint(1) NOT NULL DEFAULT '1',
  `tanggal_faktur_pajak` date DEFAULT NULL,
  `tanggal_invoice` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_lpb_batch`
--

CREATE TABLE `tb_lpb_batch` (
  `id_batch` int NOT NULL,
  `id_detail_lpb` int DEFAULT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `qty` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_lpb_detail`
--

CREATE TABLE `tb_lpb_detail` (
  `id_detail_lpb` int NOT NULL,
  `id_lpb` int DEFAULT NULL,
  `kd_barang` varchar(100) DEFAULT NULL,
  `qty_diterima` decimal(18,2) DEFAULT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `input_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `harga_satuan` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `harga_satuan_sebelumnya` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `harga_update_at` datetime DEFAULT NULL,
  `harga_update_by` varchar(100) DEFAULT NULL,
  `harga_verified_at` datetime DEFAULT NULL,
  `harga_verified_by` varchar(100) DEFAULT NULL,
  `total_harga` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total_harga_sebelumnya` decimal(18,4) NOT NULL DEFAULT '0.0000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_lpb_log`
--

CREATE TABLE `tb_lpb_log` (
  `id_log` int NOT NULL,
  `kd_po` varchar(50) NOT NULL,
  `no_invoice` varchar(100) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `keterangan` text,
  `dilakukan_oleh` varchar(100) NOT NULL,
  `dilakukan_pada` datetime DEFAULT CURRENT_TIMESTAMP,
  `checker_by` varchar(50) DEFAULT NULL,
  `checker_name` varchar(100) DEFAULT NULL,
  `data_after` text,
  `data_before` text,
  `id_lpb` int DEFAULT NULL,
  `status_after` varchar(20) DEFAULT NULL,
  `status_before` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_lpb_price_adjustment`
--

CREATE TABLE `tb_lpb_price_adjustment` (
  `id_adjustment` int NOT NULL,
  `no_adjustment` varchar(50) NOT NULL,
  `id_lpb_salah` int NOT NULL,
  `id_lpb_adjustment` int DEFAULT NULL,
  `id_retur_pembelian` int DEFAULT NULL,
  `nomor_lpb_salah` varchar(50) DEFAULT NULL,
  `nomor_lpb_adjustment` varchar(50) DEFAULT NULL,
  `tanggal_adjustment` date NOT NULL,
  `kd_po` varchar(100) DEFAULT NULL,
  `no_po` varchar(100) DEFAULT NULL,
  `kd_supplier` varchar(100) DEFAULT NULL,
  `gudang_id` varchar(30) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'DRAFT',
  `alasan_adjustment` text,
  `total_qty` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_lpb_salah` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total_lpb_benar` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `selisih_dpp` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `selisih_ppn` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `selisih_total` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `id_jurnal_lpb_adjustment` int DEFAULT NULL,
  `id_jurnal_prpp` int DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `posted_by` varchar(100) DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_lpb_price_adjustment_detail`
--

CREATE TABLE `tb_lpb_price_adjustment_detail` (
  `id_adjustment_detail` int NOT NULL,
  `id_adjustment` int NOT NULL,
  `id_detail_lpb_salah` int NOT NULL,
  `id_detail_lpb_adjustment` int DEFAULT NULL,
  `kd_barang` varchar(100) NOT NULL,
  `nama_barang` varchar(255) DEFAULT NULL,
  `qty` decimal(18,2) NOT NULL DEFAULT '0.00',
  `no_lot_adjustment` varchar(50) DEFAULT NULL,
  `expired_date_adjustment` date DEFAULT NULL,
  `harga_salah` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `harga_benar` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `dpp_salah` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `dpp_benar` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `ppn_salah` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `ppn_benar` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total_salah` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total_benar` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `kelompok_dagang` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_master_barang`
--

CREATE TABLE `tb_master_barang` (
  `id` int NOT NULL,
  `kd_system` varchar(25) NOT NULL,
  `kode_barang` varchar(25) NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `nm_barang` varchar(255) NOT NULL,
  `bhn_aktif` text NOT NULL,
  `satuan` text NOT NULL,
  `p` int NOT NULL,
  `l` int NOT NULL,
  `t` int NOT NULL,
  `berat` double NOT NULL,
  `kubikasi` double NOT NULL,
  `qty_min` int NOT NULL,
  `status` int NOT NULL,
  `kordinat` text NOT NULL,
  `akses_lv` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_master_barang_all`
--

CREATE TABLE `tb_master_barang_all` (
  `id` int NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `kode_barang_system` varchar(50) NOT NULL,
  `nama_barang` text NOT NULL,
  `kd_supplier` varchar(25) NOT NULL,
  `bhn_aktif` text NOT NULL,
  `satuan` text NOT NULL,
  `p` int NOT NULL,
  `l` int NOT NULL,
  `t` int NOT NULL,
  `berat` int NOT NULL,
  `kubikasi` text NOT NULL,
  `qty_min` int NOT NULL,
  `id_gudang` int DEFAULT NULL,
  `id_wilayah` int DEFAULT NULL,
  `hpp` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_mbarang`
--

CREATE TABLE `tb_mbarang` (
  `id` int NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `nm_barang` varchar(255) NOT NULL,
  `dimensi` int NOT NULL,
  `status` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_menu`
--

CREATE TABLE `tb_menu` (
  `id` int NOT NULL,
  `nama_menu` varchar(100) DEFAULT NULL,
  `url_menu` varchar(255) DEFAULT NULL,
  `icon_menu` varchar(100) DEFAULT NULL,
  `urutan` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_mutasi`
--

CREATE TABLE `tb_mutasi` (
  `id` int NOT NULL,
  `noreff` varchar(255) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `gudang_asal` int NOT NULL,
  `gudang_mutasi` int NOT NULL,
  `keterangan` text NOT NULL,
  `inputer` varchar(255) NOT NULL,
  `status` enum('POSTED','UNPOST','ROLLBACK','HOLD') NOT NULL,
  `input_at` text NOT NULL,
  `last_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_notifikasi`
--

CREATE TABLE `tb_notifikasi` (
  `id` int NOT NULL,
  `type` varchar(20) NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_op_driver`
--

CREATE TABLE `tb_op_driver` (
  `id` int NOT NULL,
  `kd_driver` varchar(25) NOT NULL,
  `nama_driver` text NOT NULL,
  `status` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_op_helper`
--

CREATE TABLE `tb_op_helper` (
  `id` int NOT NULL,
  `kd_helper` varchar(25) NOT NULL,
  `nama_helper` text NOT NULL,
  `status` varchar(25) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_op_plat`
--

CREATE TABLE `tb_op_plat` (
  `id` int NOT NULL,
  `noplat` varchar(25) NOT NULL,
  `nm_truk` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_order_tracking_driver`
--

CREATE TABLE `tb_order_tracking_driver` (
  `id` int NOT NULL,
  `kd_order` varchar(25) NOT NULL,
  `tgl_jalan` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pemenang`
--

CREATE TABLE `tb_pemenang` (
  `id` int NOT NULL,
  `kat_undi` varchar(25) NOT NULL,
  `noundi` int NOT NULL,
  `nama_win` text NOT NULL,
  `ket_undi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pengajuan_od`
--

CREATE TABLE `tb_pengajuan_od` (
  `id` int NOT NULL,
  `id_customer` varchar(50) DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `target_tanggal_jatuh_tempo` date DEFAULT NULL,
  `progres_penyelesaian` varchar(255) DEFAULT NULL,
  `sisa_nota` varchar(255) DEFAULT NULL,
  `komitmen_penyelesaian` text,
  `catatan` text,
  `lampiran_sc` varchar(255) DEFAULT NULL,
  `lampiran_mngtc` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending_mngsc',
  `approval_mngsc_by` varchar(100) DEFAULT NULL,
  `approval_mngsc_at` datetime DEFAULT NULL,
  `catatan_mngsc` text,
  `approval_mngtc_by` varchar(100) DEFAULT NULL,
  `approval_mngtc_at` datetime DEFAULT NULL,
  `catatan_mngtc` text,
  `approval_kadepsc_by` varchar(100) DEFAULT NULL,
  `approval_kadepsc_at` datetime DEFAULT NULL,
  `catatan_kadepsc` text,
  `create_by` varchar(100) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_by` varchar(100) DEFAULT NULL,
  `update_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pengajuan_od_faktur`
--

CREATE TABLE `tb_pengajuan_od_faktur` (
  `id` int NOT NULL,
  `id_pengajuan` int NOT NULL,
  `id_faktur` int NOT NULL,
  `no_faktur` varchar(30) NOT NULL,
  `tempo_lama` int NOT NULL,
  `tempo_baru` int NOT NULL,
  `tanggal_jatuh_tempo_baru` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_penilaian_karakter_assignment`
--

CREATE TABLE `tb_penilaian_karakter_assignment` (
  `id_assignment` int NOT NULL,
  `id_user_dinilai` int NOT NULL,
  `id_penilai` int NOT NULL,
  `id_atasan` int NOT NULL,
  `bulan` varchar(7) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '2026-07',
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_penilaian_karakter_response`
--

CREATE TABLE `tb_penilaian_karakter_response` (
  `id_response` int NOT NULL,
  `id_assignment` int NOT NULL,
  `bulan` varchar(7) COLLATE utf8mb4_general_ci NOT NULL,
  `q1_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q1_fakta` text COLLATE utf8mb4_general_ci,
  `q2_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q2_fakta` text COLLATE utf8mb4_general_ci,
  `q3_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q3_fakta` text COLLATE utf8mb4_general_ci,
  `q4_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q4_fakta` text COLLATE utf8mb4_general_ci,
  `q5_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q5_fakta` text COLLATE utf8mb4_general_ci,
  `q6_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q6_fakta` text COLLATE utf8mb4_general_ci,
  `q7_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q7_fakta` text COLLATE utf8mb4_general_ci,
  `q8_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q8_fakta` text COLLATE utf8mb4_general_ci,
  `q9_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q9_fakta` text COLLATE utf8mb4_general_ci,
  `q10_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q10_fakta` text COLLATE utf8mb4_general_ci,
  `q11_jawaban` enum('Ya','Tidak') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `q11_fakta` text COLLATE utf8mb4_general_ci,
  `submitted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pnd_do`
--

CREATE TABLE `tb_pnd_do` (
  `id` int NOT NULL,
  `id_pre_do` int NOT NULL,
  `kd_do` varchar(25) NOT NULL,
  `kd_faktur` varchar(25) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `kd_rute` text NOT NULL,
  `kd_customer` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `nm_barang` text NOT NULL,
  `qty` int NOT NULL,
  `satuan` text NOT NULL,
  `no_lot` text NOT NULL,
  `tgl_exp` text NOT NULL,
  `nominal_p` double NOT NULL,
  `jtempo` int NOT NULL,
  `barang_sts` int NOT NULL,
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_po_pending`
--

CREATE TABLE `tb_po_pending` (
  `id` int NOT NULL,
  `nopo` text NOT NULL,
  `tanggal` text NOT NULL,
  `kd_sup` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `qty_order` int NOT NULL,
  `qty_order_success` int NOT NULL,
  `qty_kurang` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_po_received`
--

CREATE TABLE `tb_po_received` (
  `id_detail_lpb` int NOT NULL,
  `no_po` varchar(100) NOT NULL,
  `kd_po` varchar(100) DEFAULT NULL,
  `kd_barang` varchar(35) NOT NULL,
  `qty_diterima` int NOT NULL DEFAULT '0',
  `satuan` varchar(50) DEFAULT NULL,
  `no_lot` varchar(100) DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pre_do`
--

CREATE TABLE `tb_pre_do` (
  `id` int NOT NULL,
  `kdupdate` varchar(25) NOT NULL,
  `tgl_inputer` text NOT NULL,
  `kd_faktur` varchar(25) NOT NULL,
  `kd_rute` text NOT NULL,
  `salesman` text NOT NULL,
  `kd_customer` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `nama_barang` text NOT NULL,
  `qty` int NOT NULL,
  `satuan` text NOT NULL,
  `no_lot` text NOT NULL,
  `tgl_exp` text NOT NULL,
  `nominal_p` double NOT NULL,
  `jtempo` int NOT NULL,
  `upload_sts` text NOT NULL,
  `data_sts` int NOT NULL,
  `barang_sts` int NOT NULL,
  `delivery_at` date NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pre_po`
--

CREATE TABLE `tb_pre_po` (
  `id_pre_po` int NOT NULL,
  `no_po` text NOT NULL,
  `kd_po` varchar(50) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `kd_suplier` varchar(35) NOT NULL,
  `kd_barang` varchar(35) NOT NULL,
  `satuan` text NOT NULL,
  `qty` int NOT NULL,
  `hrg_satuan` int NOT NULL,
  `harga_total` int NOT NULL,
  `status` int NOT NULL,
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pre_po_adjustment_log`
--

CREATE TABLE `tb_pre_po_adjustment_log` (
  `id_log` int NOT NULL,
  `kd_po` varchar(50) NOT NULL,
  `kd_barang` varchar(35) NOT NULL,
  `harga_satuan_lama` decimal(15,2) NOT NULL,
  `harga_satuan_baru` decimal(15,2) NOT NULL,
  `harga_total_lama` decimal(15,2) NOT NULL,
  `harga_total_baru` decimal(15,2) NOT NULL,
  `alasan` text,
  `dilakukan_oleh` varchar(100) NOT NULL,
  `dilakukan_pada` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pre_po_diskon_history`
--

CREATE TABLE `tb_pre_po_diskon_history` (
  `id` int NOT NULL,
  `kd_po` varchar(255) NOT NULL,
  `id_diskon_source` int DEFAULT NULL,
  `kd_suplier` varchar(35) DEFAULT NULL,
  `no_po` varchar(255) DEFAULT NULL,
  `tgl_transaksi` varchar(25) DEFAULT NULL,
  `nama_suplier` varchar(255) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `nominal` double NOT NULL DEFAULT '0',
  `source_payload` text,
  `synced_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pre_po_invoice_adjustment`
--

CREATE TABLE `tb_pre_po_invoice_adjustment` (
  `id` int NOT NULL,
  `no_po` varchar(255) DEFAULT NULL,
  `kd_po` varchar(255) NOT NULL,
  `tgl_transaksi` varchar(25) DEFAULT NULL,
  `kd_suplier` varchar(35) DEFAULT NULL,
  `kd_barang` varchar(35) NOT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `qty` double NOT NULL DEFAULT '0',
  `harga_satuan` double NOT NULL DEFAULT '0',
  `harga` double NOT NULL DEFAULT '0',
  `harga_diskon` double NOT NULL DEFAULT '0',
  `total_harga` double NOT NULL DEFAULT '0',
  `total_harga_diskon` double NOT NULL DEFAULT '0',
  `tax_percent` double NOT NULL DEFAULT '0',
  `tax` double NOT NULL DEFAULT '0',
  `tax_diskon` double NOT NULL DEFAULT '0',
  `grand_total` double NOT NULL DEFAULT '0',
  `grand_total_diskon` double NOT NULL DEFAULT '0',
  `source_payload` text,
  `synced_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_qty_lot`
--

CREATE TABLE `tb_qty_lot` (
  `id` int NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `nm_barang` varchar(255) NOT NULL,
  `gudang` text NOT NULL,
  `qty` int NOT NULL,
  `unit` text NOT NULL,
  `no_lot` text NOT NULL,
  `exp_date` text NOT NULL,
  `suplier` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_req_opname`
--

CREATE TABLE `tb_req_opname` (
  `id` int NOT NULL,
  `nama_barang` text NOT NULL,
  `exp_date` text NOT NULL,
  `qty` int NOT NULL,
  `qty_box` int NOT NULL,
  `qty_pcs` int NOT NULL,
  `inputer` text NOT NULL,
  `tim` int NOT NULL,
  `wilayah` int NOT NULL,
  `status` int NOT NULL,
  `acc_with` text NOT NULL,
  `input_at` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_retur_barang`
--

CREATE TABLE `tb_retur_barang` (
  `id` int NOT NULL,
  `type_retur` int NOT NULL,
  `kd_retur` varchar(50) NOT NULL,
  `keterangan` text NOT NULL,
  `status` int NOT NULL,
  `input_by` text NOT NULL,
  `input_at` datetime NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_retur_pembelian`
--

CREATE TABLE `tb_retur_pembelian` (
  `id_retur_pembelian` int NOT NULL,
  `no_retur_pembelian` varchar(50) NOT NULL,
  `id_lpb` int NOT NULL,
  `kd_po` varchar(100) DEFAULT NULL,
  `no_po` varchar(100) DEFAULT NULL,
  `kd_supplier` varchar(100) DEFAULT NULL,
  `tanggal_retur` date NOT NULL,
  `gudang_id` varchar(30) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'DRAFT',
  `jenis_penyelesaian` varchar(40) NOT NULL DEFAULT 'POTONG_HUTANG',
  `alasan_retur` text,
  `catatan_purchasing` text,
  `catatan_accounting` text,
  `total_dpp` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total_ppn` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `grand_total` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `id_jurnal` int DEFAULT NULL,
  `id_jurnal_reversal` int DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `submitted_by` varchar(100) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `purchasing_verified_by` varchar(100) DEFAULT NULL,
  `purchasing_verified_at` datetime DEFAULT NULL,
  `accounting_verified_by` varchar(100) DEFAULT NULL,
  `accounting_verified_at` datetime DEFAULT NULL,
  `posted_by` varchar(100) DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `reversed_by` varchar(100) DEFAULT NULL,
  `reversed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_retur_pembelian_detail`
--

CREATE TABLE `tb_retur_pembelian_detail` (
  `id_detail_retur_pembelian` int NOT NULL,
  `id_retur_pembelian` int NOT NULL,
  `id_detail_lpb` int NOT NULL,
  `kd_barang` varchar(100) NOT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `qty_retur` decimal(18,2) NOT NULL DEFAULT '0.00',
  `harga_satuan` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `dpp` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `ppn` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `kelompok_dagang` varchar(10) DEFAULT NULL,
  `alasan_retur` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_retur_pembelian_log`
--

CREATE TABLE `tb_retur_pembelian_log` (
  `id_log` bigint NOT NULL,
  `id_retur_pembelian` int DEFAULT NULL,
  `no_retur_pembelian` varchar(50) DEFAULT NULL,
  `action_type` varchar(40) NOT NULL,
  `status_before` varchar(30) DEFAULT NULL,
  `status_after` varchar(30) DEFAULT NULL,
  `data_before` longtext,
  `data_after` longtext,
  `keterangan` text,
  `dilakukan_oleh` varchar(100) DEFAULT NULL,
  `dilakukan_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_rutecs`
--

CREATE TABLE `tb_rutecs` (
  `id_rute` int NOT NULL,
  `kd_rute` text NOT NULL,
  `keterangan` text NOT NULL,
  `jenis_rute` enum('LK','KK') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_saldo_awal`
--

CREATE TABLE `tb_saldo_awal` (
  `id` int NOT NULL,
  `kode_barang_system` varchar(30) DEFAULT NULL,
  `kode_barang_zahir` varchar(30) NOT NULL,
  `nama_barang` text NOT NULL,
  `wilayah_id` int NOT NULL,
  `koordinat_id` int NOT NULL,
  `barang_pic` varchar(11) NOT NULL,
  `qty` decimal(10,0) NOT NULL,
  `nolot` text NOT NULL,
  `exp_date` text NOT NULL,
  `noreff` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_satuan`
--

CREATE TABLE `tb_satuan` (
  `id_satuan` int NOT NULL,
  `nm_satuan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_schedule_dirut`
--

CREATE TABLE `tb_schedule_dirut` (
  `id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jam` text NOT NULL,
  `suplier` text NOT NULL,
  `pic` text NOT NULL,
  `estimasi_end` text NOT NULL,
  `tujuan` text NOT NULL,
  `status` int NOT NULL,
  `keterangan` text NOT NULL,
  `create_at` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_service_truk`
--

CREATE TABLE `tb_service_truk` (
  `id` int NOT NULL,
  `kd_truk` varchar(50) NOT NULL,
  `no_pol` varchar(50) NOT NULL,
  `thn_kendaran` int NOT NULL,
  `png_jawab` text NOT NULL,
  `km_sekarang` int NOT NULL,
  `km_sebelum` int NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_sblm` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_set_tax`
--

CREATE TABLE `tb_set_tax` (
  `id_tax` int NOT NULL,
  `nm_tax` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_sop`
--

CREATE TABLE `tb_sop` (
  `id_sop` int NOT NULL,
  `nama_sop` varchar(255) NOT NULL,
  `kode_sop` varchar(50) NOT NULL,
  `tipe_sop` varchar(50) NOT NULL,
  `namafile_sop` varchar(255) NOT NULL,
  `is_karisma` int NOT NULL,
  `is_prioritas` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_spr_detail`
--

CREATE TABLE `tb_spr_detail` (
  `id_spr_detail` int UNSIGNED NOT NULL,
  `id_spr` int UNSIGNED NOT NULL,
  `no_urut` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `nama_barang` varchar(250) DEFAULT NULL,
  `no_faktur` varchar(80) DEFAULT NULL,
  `no_batch` varchar(80) DEFAULT NULL,
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
  `alasan_lainlain` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_spr_header`
--

CREATE TABLE `tb_spr_header` (
  `id_spr` int UNSIGNED NOT NULL,
  `no_spr` varchar(30) NOT NULL,
  `tanggal` date NOT NULL,
  `kd_customer` varchar(30) DEFAULT NULL,
  `nama_customer` varchar(200) DEFAULT NULL,
  `alamat` varchar(300) DEFAULT NULL,
  `nama_sales` varchar(150) DEFAULT NULL,
  `catatan` text,
  `status` enum('draft','diajukan','diverifikasi_koor','dicek_admin_stock','disetujui_kadep','selesai','ditolak') NOT NULL DEFAULT 'draft',
  `koor_sc_by` varchar(150) DEFAULT NULL,
  `koor_sc_at` datetime DEFAULT NULL,
  `koor_sc_catatan` text,
  `admin_stock_by` varchar(150) DEFAULT NULL,
  `admin_stock_at` datetime DEFAULT NULL,
  `admin_stock_catatan` text,
  `kadep_sc_by` varchar(150) DEFAULT NULL,
  `kadep_sc_at` datetime DEFAULT NULL,
  `kadep_sc_catatan` text,
  `logistik_by` varchar(150) DEFAULT NULL,
  `logistik_at` datetime DEFAULT NULL,
  `logistik_catatan` text,
  `create_by` varchar(150) DEFAULT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_by` varchar(150) DEFAULT NULL,
  `update_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_ss`
--

CREATE TABLE `tb_ss` (
  `id_poinss` int NOT NULL,
  `id_user` int NOT NULL,
  `poin_ss` varchar(255) NOT NULL,
  `tipe_ss` enum('umum','teknis') NOT NULL DEFAULT 'umum',
  `original_poin_ss` varchar(255) DEFAULT NULL,
  `is_edited` tinyint(1) NOT NULL DEFAULT '0',
  `edited_by` int DEFAULT NULL,
  `edited_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_sspoin`
--

CREATE TABLE `tb_sspoin` (
  `id_sspoin` int NOT NULL,
  `id_user` int NOT NULL,
  `id_ss` int NOT NULL,
  `poinss` varchar(255) NOT NULL,
  `nilai1` varchar(255) DEFAULT NULL,
  `nilai2` varchar(255) DEFAULT NULL,
  `nilai3` varchar(255) DEFAULT NULL,
  `nilai4` varchar(255) DEFAULT NULL,
  `nilaiss` double NOT NULL DEFAULT '0',
  `deskripsi` text NOT NULL,
  `original_poinss` text,
  `original_nilaiss` decimal(10,2) DEFAULT NULL,
  `original_deskripsi` text,
  `is_edited` tinyint(1) NOT NULL DEFAULT '0',
  `edited_by` int DEFAULT NULL,
  `edited_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_ss_history`
--

CREATE TABLE `tb_ss_history` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `id_ss` int NOT NULL,
  `id_sspoin` int NOT NULL,
  `bulan` varchar(7) NOT NULL,
  `kategori_ss` varchar(255) DEFAULT NULL,
  `poinss` text,
  `nilaiss` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_ss_simulasi`
--

CREATE TABLE `tb_ss_simulasi` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `id_ss` int NOT NULL,
  `id_sspoin` int NOT NULL,
  `nilaiss` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_ss_verified`
--

CREATE TABLE `tb_ss_verified` (
  `id_ss_verified` int NOT NULL,
  `id_user` int NOT NULL,
  `bulan` varchar(10) NOT NULL,
  `verified_by` int NOT NULL,
  `verified_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_stock_hold`
--

CREATE TABLE `tb_stock_hold` (
  `id` bigint NOT NULL,
  `noref` varchar(50) DEFAULT NULL,
  `kode_barang` varchar(50) DEFAULT NULL,
  `nama_barang` varchar(150) DEFAULT NULL,
  `gudang_asal` int DEFAULT NULL,
  `gudang_tujuan` int DEFAULT NULL,
  `exp_date` text,
  `qty` int DEFAULT NULL,
  `satuan` int NOT NULL,
  `sumber` enum('MUTASI','SO','ORDER') DEFAULT NULL,
  `status` enum('HOLD','RELEASE','CANCEL') DEFAULT 'HOLD',
  `input_by` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `released_at` datetime DEFAULT NULL,
  `no_lot` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_stock_status`
--

CREATE TABLE `tb_stock_status` (
  `id` int NOT NULL,
  `kd_update` text NOT NULL,
  `gudangid` int NOT NULL,
  `gudang` text NOT NULL,
  `last_update` datetime NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_suplier`
--

CREATE TABLE `tb_suplier` (
  `id` int NOT NULL,
  `kd_suplier` varchar(25) NOT NULL,
  `nama_suplier` text NOT NULL,
  `alamat_suplier` text NOT NULL,
  `no_telpon` text NOT NULL,
  `no_fax` text NOT NULL,
  `email` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_surat_peringatan`
--

CREATE TABLE `tb_surat_peringatan` (
  `id_sp` int NOT NULL,
  `id_user` int NOT NULL,
  `jenis_sp` enum('SP1','SP2','SP3') NOT NULL,
  `nomor_sp` varchar(100) NOT NULL,
  `tanggal_sp` date NOT NULL,
  `masa_berlaku_mulai` date NOT NULL,
  `masa_berlaku_selesai` date NOT NULL,
  `alasan` text NOT NULL,
  `keterangan` text,
  `file_sp` varchar(255) DEFAULT NULL,
  `status` enum('aktif','selesai','dihapus') DEFAULT 'aktif',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_tamu`
--

CREATE TABLE `tb_tamu` (
  `id` int NOT NULL,
  `tanggal` text NOT NULL,
  `nama` varchar(255) NOT NULL,
  `perusahaan` varchar(255) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `jumlahpersonil` varchar(255) NOT NULL,
  `tujuan` varchar(255) NOT NULL,
  `jammasuk` text NOT NULL,
  `jamkeluar` varchar(255) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nm_inputer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_tamu_lby`
--

CREATE TABLE `tb_tamu_lby` (
  `id` int NOT NULL,
  `tanggal` text NOT NULL,
  `nama` text NOT NULL,
  `perusahaan` varchar(255) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `jumlahpersonil` int NOT NULL,
  `tujuan` varchar(255) NOT NULL,
  `jammasuk` text NOT NULL,
  `jamkeluar` text NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nm_inputer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_terima_paket`
--

CREATE TABLE `tb_terima_paket` (
  `id` int NOT NULL,
  `tanggal` text NOT NULL,
  `kd_penerima` varchar(25) NOT NULL,
  `keterangan_1` text NOT NULL,
  `keterangan_2` text NOT NULL,
  `tanggal_terima_1` text NOT NULL,
  `tanggal_terima_2` text NOT NULL,
  `jam_terima_1` text NOT NULL,
  `jam_terima_2` text NOT NULL,
  `status` int NOT NULL,
  `inputer` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_tim`
--

CREATE TABLE `tb_tim` (
  `id` int NOT NULL,
  `nama_tim` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_tmp_detaildo`
--

CREATE TABLE `tb_tmp_detaildo` (
  `id` int NOT NULL,
  `id_pre_do` int NOT NULL,
  `kd_do` varchar(25) NOT NULL,
  `kd_faktur` varchar(25) NOT NULL,
  `tgl_transaksi` text NOT NULL,
  `kd_rute` text NOT NULL,
  `kd_customer` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `nm_barang` text NOT NULL,
  `qty` int NOT NULL,
  `satuan` text NOT NULL,
  `no_lot` text NOT NULL,
  `tgl_exp` text NOT NULL,
  `nominal_p` double NOT NULL,
  `note_faktur` text,
  `jtempo` int NOT NULL,
  `barang_sts` int NOT NULL,
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_tmp_do`
--

CREATE TABLE `tb_tmp_do` (
  `id` int NOT NULL,
  `norut_do` int NOT NULL,
  `kd_do` varchar(25) NOT NULL,
  `kd_faktur` varchar(25) DEFAULT NULL,
  `input_at` datetime NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_tmp_lap_distribusi`
--

CREATE TABLE `tb_tmp_lap_distribusi` (
  `id_lap_dis` int NOT NULL,
  `kd_deliveri` varchar(25) NOT NULL,
  `tgl_jalan` text NOT NULL,
  `kd_driver` varchar(25) NOT NULL,
  `kd_helper` varchar(25) NOT NULL,
  `kd_truk` varchar(25) NOT NULL,
  `destinasi` text NOT NULL,
  `tgl_masuk` text NOT NULL,
  `jm_masuk` text NOT NULL,
  `km_masuk` int NOT NULL,
  `tgl_keluar` text NOT NULL,
  `jm_keluar` text NOT NULL,
  `km_keluar` int NOT NULL,
  `status` text NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_tmp_mutasi`
--

CREATE TABLE `tb_tmp_mutasi` (
  `id` int NOT NULL,
  `nama_barang` text NOT NULL,
  `exp_date` text NOT NULL,
  `qty` int NOT NULL,
  `satuan_id` int NOT NULL,
  `user_inputer` varchar(25) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `gudang_asal` int DEFAULT NULL,
  `kode_barang` varchar(50) DEFAULT NULL,
  `kode_barang_system` varchar(50) DEFAULT NULL,
  `no_lot` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_tmp_po_received`
--

CREATE TABLE `tb_tmp_po_received` (
  `id_tmp_recieved` int NOT NULL,
  `kd_po` varchar(100) NOT NULL,
  `kd_suplier` varchar(100) NOT NULL,
  `kd_barang` varchar(100) NOT NULL,
  `qty_diterima` decimal(18,2) NOT NULL DEFAULT '0.00',
  `satuan` varchar(100) NOT NULL,
  `no_lot` varchar(100) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `crete_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `harga_satuan` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `harga_satuan_kecil` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total_harga` decimal(18,4) NOT NULL DEFAULT '0.0000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_truck`
--

CREATE TABLE `tb_truck` (
  `id` int NOT NULL,
  `nm_plat` varchar(25) NOT NULL,
  `no_plat` varchar(25) NOT NULL,
  `kd_driver` varchar(25) NOT NULL,
  `nm_driver` text NOT NULL,
  `status` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_truk`
--

CREATE TABLE `tb_truk` (
  `id` int NOT NULL,
  `nolambung` varchar(25) DEFAULT NULL,
  `noplat` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_user`
--

CREATE TABLE `tb_user` (
  `id` int NOT NULL,
  `nik` varchar(30) NOT NULL,
  `kode_user` varchar(25) NOT NULL,
  `nama_user` text NOT NULL,
  `nama_lengkap` text NOT NULL,
  `departemen` text NOT NULL,
  `alamat` text NOT NULL,
  `tgl_lahir` text NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `akses_lv` int NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_users`
--

CREATE TABLE `tb_users` (
  `id` int NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` int NOT NULL DEFAULT '1',
  `jobdesk_hrd` varchar(100) DEFAULT NULL,
  `nama_lngkp` varchar(255) NOT NULL,
  `nik` varchar(255) NOT NULL,
  `bagian` varchar(255) NOT NULL,
  `departement` varchar(255) NOT NULL,
  `jabatan` varchar(255) NOT NULL,
  `atasan` varchar(255) NOT NULL,
  `penilai` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `default_redirect` varchar(180) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_user_level_mapping`
--

CREATE TABLE `tb_user_level_mapping` (
  `id_mapping` int NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `level` int NOT NULL COMMENT '1=Karyawan, 2=Koordinator, 3=Manager, 4=Kadep',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_whats`
--

CREATE TABLE `tb_whats` (
  `id_what` int NOT NULL,
  `id_user` int NOT NULL,
  `id_kpi` int NOT NULL,
  `tipe_what` enum('A','B') DEFAULT 'A',
  `p_what` text NOT NULL,
  `bobot` double NOT NULL,
  `target_omset` decimal(15,2) DEFAULT '0.00',
  `hasil` text,
  `nilai` double NOT NULL,
  `total` double NOT NULL,
  `is_edited` tinyint(1) DEFAULT '0',
  `edited_by` int DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `original_p_what` text,
  `original_bobot` double DEFAULT NULL,
  `original_hasil` text,
  `original_nilai` double DEFAULT NULL,
  `original_total` double DEFAULT NULL,
  `original_target_omset` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_wilayah`
--

CREATE TABLE `tb_wilayah` (
  `id` int NOT NULL,
  `nama_wilayah` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `trashbin_do`
--

CREATE TABLE `trashbin_do` (
  `id` int NOT NULL,
  `tgl_inputer` text NOT NULL,
  `kd_faktur` varchar(25) NOT NULL,
  `kd_customer` varchar(25) NOT NULL,
  `kd_barang` varchar(25) NOT NULL,
  `qty` int NOT NULL,
  `satuan` text NOT NULL,
  `no_lot` text NOT NULL,
  `tgl_exp` text NOT NULL,
  `upload_sts` text NOT NULL,
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('sales','sales_executive','approver1','approver2','approver3','approver4','approver5','approver6','viewer','piutang_manager') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `cctv_tracking`
--
ALTER TABLE `cctv_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tgl` (`tgl`),
  ADD KEY `idx_lokasi` (`lokasi`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `stockopname_master`
--
ALTER TABLE `stockopname_master`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kdbarang` (`kode_barang`),
  ADD KEY `fk_nmbarang` (`nama_barang`(768)),
  ADD KEY `fk_expired_date` (`expired_date`(768)),
  ADD KEY `fk_lotbr` (`no_lot`(768));

--
-- Indeks untuk tabel `stockopname_master_box`
--
ALTER TABLE `stockopname_master_box`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kdbarang` (`kode_barang`),
  ADD KEY `fk_nmbarang` (`nama_barang`(768)),
  ADD KEY `fk_expired_date` (`expired_date`(768)),
  ADD KEY `fk_lotbr` (`no_lot`(768));

--
-- Indeks untuk tabel `stockopname_master_item`
--
ALTER TABLE `stockopname_master_item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_qrcode` (`qrcode`),
  ADD KEY `idx_kode_barang` (`kode_barang`),
  ADD KEY `idx_expired_lot` (`expired_date`,`no_lot`),
  ADD KEY `idx_stockopname_master_item_barcode` (`barcode`),
  ADD KEY `idx_stockopname_qrcode_status` (`qrcode_status`),
  ADD KEY `idx_stockopname_qrcode_retry` (`qrcode_status`,`qrcode_retry_flag`);

--
-- Indeks untuk tabel `stockopname_master_manual_item`
--
ALTER TABLE `stockopname_master_manual_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_manual_master_source` (`source_id`),
  ADD KEY `idx_manual_master_barang` (`kode_barang`),
  ADD KEY `idx_manual_master_status` (`status`);

--
-- Indeks untuk tabel `stockopname_opname`
--
ALTER TABLE `stockopname_opname`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kdbarang` (`kode_barang`),
  ADD KEY `fk_nmbarang` (`nama_barang`(768)),
  ADD KEY `fk_expired_date` (`expired_date`(768)),
  ADD KEY `fk_nolot` (`nolot`(768)),
  ADD KEY `fk_wilayah` (`wilayah`),
  ADD KEY `fk_user` (`input_by`(768));

--
-- Indeks untuk tabel `stockopname_opname_log`
--
ALTER TABLE `stockopname_opname_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stockopname_opname_log_barang` (`kode_barang`),
  ADD KEY `idx_stockopname_opname_log_opname` (`opname_id`);

--
-- Indeks untuk tabel `stockopname_opname_manual`
--
ALTER TABLE `stockopname_opname_manual`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_manual_opname_master` (`manual_master_id`),
  ADD KEY `idx_manual_opname_source` (`source_id`),
  ADD KEY `idx_manual_opname_barang` (`kode_barang`);

--
-- Indeks untuk tabel `stockopname_pending`
--
ALTER TABLE `stockopname_pending`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kd_faktur_do` (`kd_faktur`),
  ADD KEY `fk_nama_barang_do` (`nama_barang`(255)),
  ADD KEY `fk_exp_date_do` (`exp_date`(255));

--
-- Indeks untuk tabel `stockopname_recyclebin_input`
--
ALTER TABLE `stockopname_recyclebin_input`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recycle_source` (`source_id`),
  ADD KEY `idx_recycle_barang` (`kode_barang`),
  ADD KEY `idx_recycle_deleted_at` (`deleted_at`);

--
-- Indeks untuk tabel `tbar_archive`
--
ALTER TABLE `tbar_archive`
  ADD PRIMARY KEY (`id_archive`),
  ADD UNIQUE KEY `unique_archive` (`bulan`,`id_user`),
  ADD KEY `idx_user_status` (`id_user`);

--
-- Indeks untuk tabel `tbar_bobotkpi`
--
ALTER TABLE `tbar_bobotkpi`
  ADD PRIMARY KEY (`idbobotkpi`);

--
-- Indeks untuk tabel `tbar_hows`
--
ALTER TABLE `tbar_hows`
  ADD PRIMARY KEY (`id_how`);

--
-- Indeks untuk tabel `tbar_indikator_hows`
--
ALTER TABLE `tbar_indikator_hows`
  ADD PRIMARY KEY (`id_indikator`),
  ADD KEY `idx_id_how` (`id_how`),
  ADD KEY `idx_urutan` (`urutan`);

--
-- Indeks untuk tabel `tbar_indikator_whats`
--
ALTER TABLE `tbar_indikator_whats`
  ADD PRIMARY KEY (`id_indikator`),
  ADD KEY `id_what` (`id_what`);

--
-- Indeks untuk tabel `tbar_kpi`
--
ALTER TABLE `tbar_kpi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbar_sp_archive`
--
ALTER TABLE `tbar_sp_archive`
  ADD PRIMARY KEY (`id_sp_archive`),
  ADD KEY `id_archive` (`id_archive`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `tbar_whats`
--
ALTER TABLE `tbar_whats`
  ADD PRIMARY KEY (`id_what`);

--
-- Indeks untuk tabel `tberp_stock_batch`
--
ALTER TABLE `tberp_stock_batch`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_batch` (`kd_barang`,`gudang_id`,`no_lot`,`expired_date`);

--
-- Indeks untuk tabel `tberp_stock_ledger`
--
ALTER TABLE `tberp_stock_ledger`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbhrd_environment_issues`
--
ALTER TABLE `tbhrd_environment_issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `rating_id` (`rating_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `idx_tbhrd_environment_issues_star_rating` (`star_rating`);

--
-- Indeks untuk tabel `tbhrd_issue_evidences`
--
ALTER TABLE `tbhrd_issue_evidences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issue_id` (`issue_id`);

--
-- Indeks untuk tabel `tbhrd_issue_logs`
--
ALTER TABLE `tbhrd_issue_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issue_id` (`issue_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indeks untuk tabel `tbhrd_issue_rating`
--
ALTER TABLE `tbhrd_issue_rating`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbhrd_issue_status`
--
ALTER TABLE `tbhrd_issue_status`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbhrd_lokasi`
--
ALTER TABLE `tbhrd_lokasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `tbhrd_nilai_lingkungan`
--
ALTER TABLE `tbhrd_nilai_lingkungan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tbhrd_nilai_lingkungan_location` (`location_id`),
  ADD KEY `idx_tbhrd_nilai_lingkungan_rating` (`rating_id`),
  ADD KEY `idx_tbhrd_nilai_lingkungan_status` (`status_id`),
  ADD KEY `idx_tbhrd_nilai_lingkungan_star` (`star_rating`),
  ADD KEY `idx_tbhrd_nilai_lingkungan_report_datetime` (`report_datetime`);

--
-- Indeks untuk tabel `tbkeu_akun`
--
ALTER TABLE `tbkeu_akun`
  ADD PRIMARY KEY (`id_akun`),
  ADD UNIQUE KEY `uk_tbkeu_akun_kode` (`kode_akun`),
  ADD KEY `idx_tbkeu_akun_klasifikasi` (`id_klasifikasi`),
  ADD KEY `idx_tbkeu_akun_parent` (`parent_id`),
  ADD KEY `idx_tbkeu_akun_tipe_status` (`tipe_akun`,`is_active`);

--
-- Indeks untuk tabel `tbkeu_akun_karismaerp_ref`
--
ALTER TABLE `tbkeu_akun_karismaerp_ref`
  ADD PRIMARY KEY (`id_ref`),
  ADD UNIQUE KEY `uk_tbkeu_akun_karismaerp_kiraan` (`kode_kiraan`),
  ADD KEY `idx_tbkeu_akun_karismaerp_akun` (`id_akun`),
  ADD KEY `idx_tbkeu_akun_karismaerp_sub` (`id_sub_klasifikasi`),
  ADD KEY `idx_tbkeu_akun_karismaerp_batch` (`id_batch`);

--
-- Indeks untuk tabel `tbkeu_dummy_source`
--
ALTER TABLE `tbkeu_dummy_source`
  ADD PRIMARY KEY (`id_dummy`),
  ADD UNIQUE KEY `uk_tbkeu_dummy_source_no` (`source_no`);

--
-- Indeks untuk tabel `tbkeu_jenis_jurnal`
--
ALTER TABLE `tbkeu_jenis_jurnal`
  ADD PRIMARY KEY (`id_jenis_jurnal`),
  ADD UNIQUE KEY `uk_tbkeu_jenis_jurnal_kode` (`kode_jenis_jurnal`),
  ADD KEY `idx_tbkeu_jenis_jurnal_active` (`is_active`);

--
-- Indeks untuk tabel `tbkeu_jurnal`
--
ALTER TABLE `tbkeu_jurnal`
  ADD PRIMARY KEY (`id_jurnal`),
  ADD UNIQUE KEY `uk_tbkeu_jurnal_nomor` (`nomor_jurnal`),
  ADD UNIQUE KEY `uk_tbkeu_jurnal_idempotency` (`idempotency_key`),
  ADD UNIQUE KEY `uk_tbkeu_jurnal_source_event` (`source_module`,`source_type`,`source_id`,`posting_event`),
  ADD KEY `idx_tbkeu_jurnal_tanggal` (`tanggal_transaksi`),
  ADD KEY `idx_tbkeu_jurnal_periode` (`id_periode`),
  ADD KEY `idx_tbkeu_jurnal_jenis` (`id_jenis_jurnal`),
  ADD KEY `idx_tbkeu_jurnal_status` (`status`),
  ADD KEY `idx_tbkeu_jurnal_source` (`source_module`,`source_type`,`source_id`,`source_no`),
  ADD KEY `idx_tbkeu_jurnal_reversal` (`reversal_of_journal_id`);

--
-- Indeks untuk tabel `tbkeu_jurnal_detail`
--
ALTER TABLE `tbkeu_jurnal_detail`
  ADD PRIMARY KEY (`id_jurnal_detail`),
  ADD UNIQUE KEY `uk_tbkeu_jurnal_detail_baris` (`id_jurnal`,`nomor_baris`),
  ADD KEY `idx_tbkeu_jurnal_detail_akun` (`id_akun`),
  ADD KEY `idx_tbkeu_jurnal_detail_dokumen` (`nomor_dokumen`);

--
-- Indeks untuk tabel `tbkeu_jurnal_log`
--
ALTER TABLE `tbkeu_jurnal_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_tbkeu_jurnal_log_jurnal` (`id_jurnal`),
  ADD KEY `idx_tbkeu_jurnal_log_action` (`action`);

--
-- Indeks untuk tabel `tbkeu_karismaerp_import_batch`
--
ALTER TABLE `tbkeu_karismaerp_import_batch`
  ADD PRIMARY KEY (`id_batch`),
  ADD UNIQUE KEY `uk_tbkeu_karismaerp_batch_code` (`batch_code`);

--
-- Indeks untuk tabel `tbkeu_karismaerp_kelompok_jurnal`
--
ALTER TABLE `tbkeu_karismaerp_kelompok_jurnal`
  ADD PRIMARY KEY (`id_kelompok_karismaerp`),
  ADD UNIQUE KEY `uk_tbkeu_karismaerp_kelompok_no` (`no_kelompok`),
  ADD KEY `idx_tbkeu_karismaerp_kelompok_kode` (`kode_kelompok`),
  ADD KEY `idx_tbkeu_karismaerp_kelompok_batch` (`id_batch`);

--
-- Indeks untuk tabel `tbkeu_kas_keluar`
--
ALTER TABLE `tbkeu_kas_keluar`
  ADD PRIMARY KEY (`id_kas_keluar`),
  ADD UNIQUE KEY `no_referensi` (`no_referensi`);

--
-- Indeks untuk tabel `tbkeu_kas_keluar_detail`
--
ALTER TABLE `tbkeu_kas_keluar_detail`
  ADD PRIMARY KEY (`id_kas_keluar_detail`),
  ADD KEY `id_kas_keluar` (`id_kas_keluar`);

--
-- Indeks untuk tabel `tbkeu_kas_masuk`
--
ALTER TABLE `tbkeu_kas_masuk`
  ADD PRIMARY KEY (`id_kas_masuk`),
  ADD UNIQUE KEY `no_referensi` (`no_referensi`);

--
-- Indeks untuk tabel `tbkeu_kas_masuk_detail`
--
ALTER TABLE `tbkeu_kas_masuk_detail`
  ADD PRIMARY KEY (`id_kas_masuk_detail`),
  ADD KEY `id_kas_masuk` (`id_kas_masuk`);

--
-- Indeks untuk tabel `tbkeu_kelompok_dagang`
--
ALTER TABLE `tbkeu_kelompok_dagang`
  ADD PRIMARY KEY (`NOINDEX`);

--
-- Indeks untuk tabel `tbkeu_klasifikasi_akun`
--
ALTER TABLE `tbkeu_klasifikasi_akun`
  ADD PRIMARY KEY (`id_klasifikasi`),
  ADD UNIQUE KEY `uk_tbkeu_klasifikasi_kode` (`kode_klasifikasi`);

--
-- Indeks untuk tabel `tbkeu_mapping_akun`
--
ALTER TABLE `tbkeu_mapping_akun`
  ADD PRIMARY KEY (`id_mapping`),
  ADD UNIQUE KEY `uk_tbkeu_mapping_rule_scope` (`source_module`,`source_type`,`scope_type`,`scope_key`,`posting_event`,`account_role`,`entry_side`),
  ADD KEY `idx_tbkeu_mapping_lookup` (`posting_event`,`account_role`,`entry_side`,`is_active`),
  ADD KEY `idx_tbkeu_mapping_akun` (`id_akun`),
  ADD KEY `idx_tbkeu_mapping_scope` (`scope_type`,`scope_key`,`posting_event`,`is_active`);

--
-- Indeks untuk tabel `tbkeu_nomor_dokumen`
--
ALTER TABLE `tbkeu_nomor_dokumen`
  ADD PRIMARY KEY (`id_nomor`),
  ADD UNIQUE KEY `uk_tbkeu_nomor_dokumen` (`kode_jenis_jurnal`,`periode_yyyymm`);

--
-- Indeks untuk tabel `tbkeu_pembayaran`
--
ALTER TABLE `tbkeu_pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `uk_tbkeu_pembayaran_nomor` (`nomor_pembayaran`),
  ADD KEY `idx_tbkeu_pembayaran_type_status` (`payment_type`,`status`,`tanggal_pembayaran`),
  ADD KEY `idx_tbkeu_pembayaran_source` (`source_module`,`source_type`,`source_id`),
  ADD KEY `idx_tbkeu_pembayaran_jurnal` (`id_jurnal`);

--
-- Indeks untuk tabel `tbkeu_pembayaran_alokasi`
--
ALTER TABLE `tbkeu_pembayaran_alokasi`
  ADD PRIMARY KEY (`id_alokasi`),
  ADD UNIQUE KEY `uk_tbkeu_payment_alloc_line` (`id_pembayaran`,`nomor_baris`),
  ADD KEY `idx_tbkeu_payment_alloc_invoice` (`invoice_source_module`,`invoice_source_type`,`invoice_source_id`,`invoice_no`);

--
-- Indeks untuk tabel `tbkeu_pembayaran_faktur`
--
ALTER TABLE `tbkeu_pembayaran_faktur`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_faktur` (`id_faktur`),
  ADD KEY `no_faktur` (`no_faktur`);

--
-- Indeks untuk tabel `tbkeu_periode_fiskal`
--
ALTER TABLE `tbkeu_periode_fiskal`
  ADD PRIMARY KEY (`id_periode`),
  ADD UNIQUE KEY `uk_tbkeu_periode_kode` (`kode_periode`),
  ADD KEY `idx_tbkeu_periode_tanggal` (`tanggal_mulai`,`tanggal_selesai`),
  ADD KEY `idx_tbkeu_periode_status` (`status`,`is_active`);

--
-- Indeks untuk tabel `tbkeu_periode_fiskal_log`
--
ALTER TABLE `tbkeu_periode_fiskal_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_tbkeu_periode_log_periode` (`id_periode`),
  ADD KEY `idx_tbkeu_periode_log_action` (`action`,`approval_at`);

--
-- Indeks untuk tabel `tbkeu_posting_exception`
--
ALTER TABLE `tbkeu_posting_exception`
  ADD PRIMARY KEY (`id_exception`),
  ADD KEY `idx_tbkeu_exception_status` (`status`,`created_at`),
  ADD KEY `idx_tbkeu_exception_source` (`source_module`,`source_type`,`source_id`,`posting_event`),
  ADD KEY `idx_tbkeu_exception_jurnal` (`id_jurnal`);

--
-- Indeks untuk tabel `tbkeu_report_rule_akun`
--
ALTER TABLE `tbkeu_report_rule_akun`
  ADD PRIMARY KEY (`id_rule`),
  ADD UNIQUE KEY `uk_tbkeu_report_rule_statement_code` (`statement_type`,`kode_rekening_normalized`),
  ADD KEY `idx_tbkeu_report_rule_code` (`kode_rekening_normalized`),
  ADD KEY `idx_tbkeu_report_rule_statement_order` (`statement_type`,`urutan`),
  ADD KEY `idx_tbkeu_report_rule_batch` (`id_batch`),
  ADD KEY `fk_tbkeu_report_rule_akun` (`id_akun`);

--
-- Indeks untuk tabel `tbkeu_saldo_awal_akun`
--
ALTER TABLE `tbkeu_saldo_awal_akun`
  ADD PRIMARY KEY (`id_saldo_awal`),
  ADD UNIQUE KEY `uk_tbkeu_saldo_awal` (`id_akun`,`tanggal_saldo`);

--
-- Indeks untuk tabel `tbkeu_saldo_normal`
--
ALTER TABLE `tbkeu_saldo_normal`
  ADD PRIMARY KEY (`kode_saldo`);

--
-- Indeks untuk tabel `tbkeu_sub_klasifikasi_akun`
--
ALTER TABLE `tbkeu_sub_klasifikasi_akun`
  ADD PRIMARY KEY (`id_sub_klasifikasi`),
  ADD UNIQUE KEY `uk_tbkeu_sub_klasifikasi_kode` (`kode_sub_klasifikasi`),
  ADD KEY `idx_tbkeu_sub_klasifikasi_klasifikasi` (`id_klasifikasi`),
  ADD KEY `idx_tbkeu_sub_klasifikasi_header` (`id_akun_header`),
  ADD KEY `idx_tbkeu_sub_klasifikasi_batch` (`id_batch`);

--
-- Indeks untuk tabel `tbkeu_tipe_kontrol`
--
ALTER TABLE `tbkeu_tipe_kontrol`
  ADD PRIMARY KEY (`kode_tipe_kontrol`);

--
-- Indeks untuk tabel `tbkmt_dca`
--
ALTER TABLE `tbkmt_dca`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dca_status_verifikasi` (`status_verifikasi`);

--
-- Indeks untuk tabel `tbkmt_dca_detail`
--
ALTER TABLE `tbkmt_dca_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detail_dca` (`id_dca`);

--
-- Indeks untuk tabel `tbkmt_dca_kegiatan`
--
ALTER TABLE `tbkmt_dca_kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbkmt_dca_verifikasi_log`
--
ALTER TABLE `tbkmt_dca_verifikasi_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_id_dca` (`id_dca`);

--
-- Indeks untuk tabel `tbkmt_gaji`
--
ALTER TABLE `tbkmt_gaji`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbkmt_omset`
--
ALTER TABLE `tbkmt_omset`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_omset_wilayah` (`id_wilayah`);

--
-- Indeks untuk tabel `tbkmt_operasional`
--
ALTER TABLE `tbkmt_operasional`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_operasional_status` (`status_verifikasi`);

--
-- Indeks untuk tabel `tbkmt_operasional_verifikasi_log`
--
ALTER TABLE `tbkmt_operasional_verifikasi_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_id_op` (`id_operasional`);

--
-- Indeks untuk tabel `tbkmt_others`
--
ALTER TABLE `tbkmt_others`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbkmt_promo_material`
--
ALTER TABLE `tbkmt_promo_material`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbkmt_retur`
--
ALTER TABLE `tbkmt_retur`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbkmt_wilayah`
--
ALTER TABLE `tbkmt_wilayah`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbpo_akun_tr`
--
ALTER TABLE `tbpo_akun_tr`
  ADD PRIMARY KEY (`id_akun`);

--
-- Indeks untuk tabel `tbpo_barang`
--
ALTER TABLE `tbpo_barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `idx_kode_barang` (`kode_barang`),
  ADD KEY `idx_kd_suplier` (`kd_suplier`);

--
-- Indeks untuk tabel `tbpo_barang_akun`
--
ALTER TABLE `tbpo_barang_akun`
  ADD PRIMARY KEY (`kode_barang`);

--
-- Indeks untuk tabel `tbpo_barang_nk`
--
ALTER TABLE `tbpo_barang_nk`
  ADD PRIMARY KEY (`id_brg_nk`),
  ADD KEY `idx_barang_nk_kd_barang` (`kd_barang`),
  ADD KEY `idx_barang_nk_kd_lokasi` (`kd_lokasi`);

--
-- Indeks untuk tabel `tbpo_barang_nk_lokasi`
--
ALTER TABLE `tbpo_barang_nk_lokasi`
  ADD PRIMARY KEY (`id_lokasi`);

--
-- Indeks untuk tabel `tbpo_barang_packaging`
--
ALTER TABLE `tbpo_barang_packaging`
  ADD PRIMARY KEY (`id_packaging`);

--
-- Indeks untuk tabel `tbpo_detail_po`
--
ALTER TABLE `tbpo_detail_po`
  ADD PRIMARY KEY (`id_det_po`);

--
-- Indeks untuk tabel `tbpo_detail_po_nk`
--
ALTER TABLE `tbpo_detail_po_nk`
  ADD PRIMARY KEY (`id_det_po_nk`);

--
-- Indeks untuk tabel `tbpo_detail_req`
--
ALTER TABLE `tbpo_detail_req`
  ADD PRIMARY KEY (`id_det_po_nk`);

--
-- Indeks untuk tabel `tbpo_diskon`
--
ALTER TABLE `tbpo_diskon`
  ADD PRIMARY KEY (`id_diskon`);

--
-- Indeks untuk tabel `tbpo_diskon_merk`
--
ALTER TABLE `tbpo_diskon_merk`
  ADD PRIMARY KEY (`id_diskon`),
  ADD KEY `idx_merk_barang` (`merk_barang`),
  ADD KEY `idx_no_po` (`no_po`);

--
-- Indeks untuk tabel `tbpo_file_bukti_beli`
--
ALTER TABLE `tbpo_file_bukti_beli`
  ADD PRIMARY KEY (`id_fk_bukti`);

--
-- Indeks untuk tabel `tbpo_file_nk`
--
ALTER TABLE `tbpo_file_nk`
  ADD PRIMARY KEY (`id_file_nk`);

--
-- Indeks untuk tabel `tbpo_formula`
--
ALTER TABLE `tbpo_formula`
  ADD PRIMARY KEY (`id_formula`),
  ADD UNIQUE KEY `kode_formula` (`kode_formula`);

--
-- Indeks untuk tabel `tbpo_formula_result`
--
ALTER TABLE `tbpo_formula_result`
  ADD PRIMARY KEY (`id_result`),
  ADD KEY `id_formula` (`id_formula`);

--
-- Indeks untuk tabel `tbpo_formula_variable`
--
ALTER TABLE `tbpo_formula_variable`
  ADD PRIMARY KEY (`id_variable`),
  ADD KEY `id_formula` (`id_formula`);

--
-- Indeks untuk tabel `tbpo_generateqrcode`
--
ALTER TABLE `tbpo_generateqrcode`
  ADD PRIMARY KEY (`id_gqrcode`);

--
-- Indeks untuk tabel `tbpo_generate_kd`
--
ALTER TABLE `tbpo_generate_kd`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbpo_generate_kd_ponk`
--
ALTER TABLE `tbpo_generate_kd_ponk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbpo_kat_br`
--
ALTER TABLE `tbpo_kat_br`
  ADD PRIMARY KEY (`id_kat_br`);

--
-- Indeks untuk tabel `tbpo_notetemplate`
--
ALTER TABLE `tbpo_notetemplate`
  ADD PRIMARY KEY (`id_nt_template`);

--
-- Indeks untuk tabel `tbpo_note_barang`
--
ALTER TABLE `tbpo_note_barang`
  ADD PRIMARY KEY (`id_nt_barang`);

--
-- Indeks untuk tabel `tbpo_note_direktur`
--
ALTER TABLE `tbpo_note_direktur`
  ADD PRIMARY KEY (`id_note`);

--
-- Indeks untuk tabel `tbpo_note_pembelian`
--
ALTER TABLE `tbpo_note_pembelian`
  ADD PRIMARY KEY (`id_nt_pembelian`);

--
-- Indeks untuk tabel `tbpo_nt_tmp_pembelian`
--
ALTER TABLE `tbpo_nt_tmp_pembelian`
  ADD PRIMARY KEY (`id_tmp_nt_pembelian`);

--
-- Indeks untuk tabel `tbpo_po`
--
ALTER TABLE `tbpo_po`
  ADD PRIMARY KEY (`id_po`);

--
-- Indeks untuk tabel `tbpo_po_nk`
--
ALTER TABLE `tbpo_po_nk`
  ADD PRIMARY KEY (`id_po_nk`);

--
-- Indeks untuk tabel `tbpo_ratings`
--
ALTER TABLE `tbpo_ratings`
  ADD PRIMARY KEY (`id_rating`);

--
-- Indeks untuk tabel `tbpo_req_masterbarang`
--
ALTER TABLE `tbpo_req_masterbarang`
  ADD PRIMARY KEY (`id_reqmbarang`);

--
-- Indeks untuk tabel `tbpo_req_nk`
--
ALTER TABLE `tbpo_req_nk`
  ADD PRIMARY KEY (`id_po_nk`);

--
-- Indeks untuk tabel `tbpo_satuan`
--
ALTER TABLE `tbpo_satuan`
  ADD PRIMARY KEY (`id_satuan`);

--
-- Indeks untuk tabel `tbpo_set_note`
--
ALTER TABLE `tbpo_set_note`
  ADD PRIMARY KEY (`id_set_note`);

--
-- Indeks untuk tabel `tbpo_set_tax`
--
ALTER TABLE `tbpo_set_tax`
  ADD PRIMARY KEY (`id_tax`);

--
-- Indeks untuk tabel `tbpo_sosialisasi`
--
ALTER TABLE `tbpo_sosialisasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbpo_suplier`
--
ALTER TABLE `tbpo_suplier`
  ADD PRIMARY KEY (`id_suplier`);

--
-- Indeks untuk tabel `tbpo_tmp_diskon`
--
ALTER TABLE `tbpo_tmp_diskon`
  ADD PRIMARY KEY (`id_tmp_diskon`);

--
-- Indeks untuk tabel `tbpo_tmp_item`
--
ALTER TABLE `tbpo_tmp_item`
  ADD PRIMARY KEY (`id_tmp`);

--
-- Indeks untuk tabel `tbpo_tmp_item_nk`
--
ALTER TABLE `tbpo_tmp_item_nk`
  ADD PRIMARY KEY (`id_tmp_nk`);

--
-- Indeks untuk tabel `tbpo_tmp_note_barang`
--
ALTER TABLE `tbpo_tmp_note_barang`
  ADD PRIMARY KEY (`id_nt_tmp_barang`);

--
-- Indeks untuk tabel `tbpo_tmp_tax`
--
ALTER TABLE `tbpo_tmp_tax`
  ADD PRIMARY KEY (`id_tmp_tax`);

--
-- Indeks untuk tabel `tbpo_tracking_po`
--
ALTER TABLE `tbpo_tracking_po`
  ADD PRIMARY KEY (`id_po_tracking`);

--
-- Indeks untuk tabel `tbpo_transaksi`
--
ALTER TABLE `tbpo_transaksi`
  ADD PRIMARY KEY (`id_transnk`),
  ADD KEY `idx_transaksi_barang_akun` (`kd_barang`,`kd_akun`),
  ADD KEY `idx_transaksi_barang_tanggal` (`kd_barang`,`tgl_transaksi`(10));

--
-- Indeks untuk tabel `tbpo_transaksi_tmp`
--
ALTER TABLE `tbpo_transaksi_tmp`
  ADD PRIMARY KEY (`id_transnk`);

--
-- Indeks untuk tabel `tbpo_transaksi_trashbin`
--
ALTER TABLE `tbpo_transaksi_trashbin`
  ADD PRIMARY KEY (`id_trashbin`);

--
-- Indeks untuk tabel `tbpo_user`
--
ALTER TABLE `tbpo_user`
  ADD PRIMARY KEY (`id_user`);

--
-- Indeks untuk tabel `tbq_module`
--
ALTER TABLE `tbq_module`
  ADD PRIMARY KEY (`id_qmodule`);

--
-- Indeks untuk tabel `tbq_nilaim`
--
ALTER TABLE `tbq_nilaim`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbq_review_pic`
--
ALTER TABLE `tbq_review_pic`
  ADD PRIMARY KEY (`id_review`);

--
-- Indeks untuk tabel `tbq_review_q`
--
ALTER TABLE `tbq_review_q`
  ADD PRIMARY KEY (`id_reviewq`);

--
-- Indeks untuk tabel `tbrp_activity_log`
--
ALTER TABLE `tbrp_activity_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_no_referensi` (`no_referensi`),
  ADD KEY `idx_tipe_referensi` (`tipe_referensi`),
  ADD KEY `idx_aksi` (`aksi`),
  ADD KEY `idx_user_by` (`user_by`);

--
-- Indeks untuk tabel `tbrp_retur_penjualan_detail`
--
ALTER TABLE `tbrp_retur_penjualan_detail`
  ADD PRIMARY KEY (`id_retur_detail`),
  ADD KEY `idx_id_retur` (`id_retur`);

--
-- Indeks untuk tabel `tbrp_retur_penjualan_header`
--
ALTER TABLE `tbrp_retur_penjualan_header`
  ADD PRIMARY KEY (`id_retur`),
  ADD UNIQUE KEY `uq_no_retur` (`no_retur`),
  ADD KEY `idx_id_spr_retur` (`id_spr`),
  ADD KEY `idx_status_retur` (`status_retur`);

--
-- Indeks untuk tabel `tbrp_spr_detail`
--
ALTER TABLE `tbrp_spr_detail`
  ADD PRIMARY KEY (`id_spr_detail`),
  ADD KEY `idx_id_spr` (`id_spr`);

--
-- Indeks untuk tabel `tbrp_spr_header`
--
ALTER TABLE `tbrp_spr_header`
  ADD PRIMARY KEY (`id_spr`),
  ADD UNIQUE KEY `uq_no_spr` (`no_spr`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_create_by` (`create_by`);

--
-- Indeks untuk tabel `tbsim_bobotkpi`
--
ALTER TABLE `tbsim_bobotkpi`
  ADD PRIMARY KEY (`idbobotkpi`);

--
-- Indeks untuk tabel `tbsim_hows`
--
ALTER TABLE `tbsim_hows`
  ADD PRIMARY KEY (`id_how`);

--
-- Indeks untuk tabel `tbsim_indikator_hows`
--
ALTER TABLE `tbsim_indikator_hows`
  ADD PRIMARY KEY (`id_indikator`),
  ADD KEY `idx_id_how` (`id_how`),
  ADD KEY `idx_urutan` (`urutan`);

--
-- Indeks untuk tabel `tbsim_indikator_whats`
--
ALTER TABLE `tbsim_indikator_whats`
  ADD PRIMARY KEY (`id_indikator`),
  ADD KEY `fk_indikator_whats` (`id_what`);

--
-- Indeks untuk tabel `tbsim_kpi`
--
ALTER TABLE `tbsim_kpi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbsim_whats`
--
ALTER TABLE `tbsim_whats`
  ADD PRIMARY KEY (`id_what`);

--
-- Indeks untuk tabel `tbso_activity_log`
--
ALTER TABLE `tbso_activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbso_approval_harga`
--
ALTER TABLE `tbso_approval_harga`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_so` (`id_so`),
  ADD KEY `id_so_detail` (`id_so_detail`),
  ADD KEY `status` (`status`);

--
-- Indeks untuk tabel `tbso_cancel_partial_request`
--
ALTER TABLE `tbso_cancel_partial_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cpreq_id_so` (`id_so`),
  ADD KEY `fk_cpreq_id_so_detail` (`id_so_detail`);

--
-- Indeks untuk tabel `tbso_faktur_detail`
--
ALTER TABLE `tbso_faktur_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_faktur` (`id_faktur`),
  ADD KEY `idx_no_faktur` (`no_faktur`),
  ADD KEY `idx_id_so` (`id_so`),
  ADD KEY `idx_id_so_detail` (`id_so_detail`),
  ADD KEY `idx_kd_barang` (`kd_barang`);

--
-- Indeks untuk tabel `tbso_faktur_jurnal`
--
ALTER TABLE `tbso_faktur_jurnal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_faktur_jurnal_id` (`id_faktur`);

--
-- Indeks untuk tabel `tbso_faktur_log`
--
ALTER TABLE `tbso_faktur_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_no_so` (`no_so`),
  ADD KEY `idx_no_faktur` (`no_faktur`),
  ADD KEY `idx_id_faktur` (`id_faktur`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `tbso_faktur_penjualan`
--
ALTER TABLE `tbso_faktur_penjualan`
  ADD PRIMARY KEY (`id_faktur`),
  ADD UNIQUE KEY `no_faktur` (`no_faktur`),
  ADD KEY `idx_id_so` (`id_so`),
  ADD KEY `idx_no_so` (`no_so`),
  ADD KEY `idx_kd_cust` (`kd_customer`);

--
-- Indeks untuk tabel `tbso_sales_order`
--
ALTER TABLE `tbso_sales_order`
  ADD PRIMARY KEY (`id_so`),
  ADD UNIQUE KEY `uk_tbso_sales_order_no_faktur` (`no_faktur`),
  ADD KEY `idx_so_status` (`status`),
  ADD KEY `idx_so_tanggal` (`tanggal_transaksi`),
  ADD KEY `idx_so_no_so` (`no_so`);

--
-- Indeks untuk tabel `tbso_sales_order_detail`
--
ALTER TABLE `tbso_sales_order_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sod_idso` (`no_so`),
  ADD KEY `idx_sod_barang` (`kd_barang`),
  ADD KEY `idx_sod_no_faktur` (`no_faktur`);

--
-- Indeks untuk tabel `tb_akses_level`
--
ALTER TABLE `tb_akses_level`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_akses_menu`
--
ALTER TABLE `tb_akses_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `akses_lv_id` (`akses_lv_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indeks untuk tabel `tb_auth`
--
ALTER TABLE `tb_auth`
  ADD PRIMARY KEY (`id_auth`);

--
-- Indeks untuk tabel `tb_barangv2`
--
ALTER TABLE `tb_barangv2`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `kode_barang` (`kode_barang`),
  ADD KEY `nama_barang` (`nama_barang`(768)),
  ADD KEY `nama_suplier` (`nama_suplier`(768)),
  ADD KEY `produk_fokus` (`produk_fokus`);

--
-- Indeks untuk tabel `tb_bobotkpi`
--
ALTER TABLE `tb_bobotkpi`
  ADD PRIMARY KEY (`idbobotkpi`);

--
-- Indeks untuk tabel `tb_bongkaran`
--
ALTER TABLE `tb_bongkaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_kode_bongkar` (`kode_bongkar`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_archived` (`is_archived`);

--
-- Indeks untuk tabel `tb_bongkaran_checker`
--
ALTER TABLE `tb_bongkaran_checker`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_bongkaran` (`id_bongkaran`),
  ADD KEY `idx_nik_checker` (`nik_checker`);

--
-- Indeks untuk tabel `tb_checklist_kendaraan`
--
ALTER TABLE `tb_checklist_kendaraan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_checklist_kendaraan_detail`
--
ALTER TABLE `tb_checklist_kendaraan_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`);

--
-- Indeks untuk tabel `tb_checkup_mekanik_kategori`
--
ALTER TABLE `tb_checkup_mekanik_kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `tb_checkup_mekanik_kategori_detail`
--
ALTER TABLE `tb_checkup_mekanik_kategori_detail`
  ADD PRIMARY KEY (`id_detail_kat`);

--
-- Indeks untuk tabel `tb_checkup_mekanik_kategori_foto`
--
ALTER TABLE `tb_checkup_mekanik_kategori_foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ckup` (`id_ckup`),
  ADD KEY `idx_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `tb_customer`
--
ALTER TABLE `tb_customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_customer` (`kd_customer`),
  ADD KEY `idx_customer` (`kd_customer`);

--
-- Indeks untuk tabel `tb_customer_list_undian`
--
ALTER TABLE `tb_customer_list_undian`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_dailystock`
--
ALTER TABLE `tb_dailystock`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_dailystock_global`
--
ALTER TABLE `tb_dailystock_global`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_suplier` (`kd_suplier`),
  ADD KEY `fk_barang` (`kd_barang`) USING BTREE;

--
-- Indeks untuk tabel `tb_departemen`
--
ALTER TABLE `tb_departemen`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_detail_do`
--
ALTER TABLE `tb_detail_do`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_customer` (`kd_customer`),
  ADD KEY `fk_faktur` (`kd_faktur`),
  ADD KEY `kd_barang` (`kd_barang`),
  ADD KEY `fk_do` (`kd_do`),
  ADD KEY `idx_detail_faktur` (`kd_faktur`);

--
-- Indeks untuk tabel `tb_detail_mutasi`
--
ALTER TABLE `tb_detail_mutasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mutasi_out` (`gdg_asal`,`kode_barang`,`exp_date`(30)),
  ADD KEY `idx_mutasi_in` (`gdg_mutasi`,`kode_barang`,`exp_date`(30),`kode_barang_zahir`) USING BTREE;

--
-- Indeks untuk tabel `tb_detail_retur_barang`
--
ALTER TABLE `tb_detail_retur_barang`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_det_tracking_driver`
--
ALTER TABLE `tb_det_tracking_driver`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_do`
--
ALTER TABLE `tb_do`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kd_do` (`kd_do`),
  ADD KEY `idx_tb_do_sales_confirm` (`sales_confirm_status`,`status`);

--
-- Indeks untuk tabel `tb_editlog_faktur`
--
ALTER TABLE `tb_editlog_faktur`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_eviden`
--
ALTER TABLE `tb_eviden`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_eviden_backup`
--
ALTER TABLE `tb_eviden_backup`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_expedisi`
--
ALTER TABLE `tb_expedisi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_feedback`
--
ALTER TABLE `tb_feedback`
  ADD PRIMARY KEY (`id_feedback`),
  ADD KEY `id_user_pemberi` (`id_user_pemberi`),
  ADD KEY `id_user_penerima` (`id_user_penerima`),
  ADD KEY `bulan` (`bulan`);

--
-- Indeks untuk tabel `tb_gbrupload_cheklist`
--
ALTER TABLE `tb_gbrupload_cheklist`
  ADD PRIMARY KEY (`id_upload`);

--
-- Indeks untuk tabel `tb_gudang`
--
ALTER TABLE `tb_gudang`
  ADD PRIMARY KEY (`id_gudang`);

--
-- Indeks untuk tabel `tb_gudang_wilayah`
--
ALTER TABLE `tb_gudang_wilayah`
  ADD PRIMARY KEY (`id_wilayah`),
  ADD KEY `id_gudang` (`id_gudang`);

--
-- Indeks untuk tabel `tb_hows`
--
ALTER TABLE `tb_hows`
  ADD PRIMARY KEY (`id_how`);

--
-- Indeks untuk tabel `tb_ics`
--
ALTER TABLE `tb_ics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_nmbarang` (`nama_barang`(255)),
  ADD KEY `kd_system` (`kd_system`);

--
-- Indeks untuk tabel `tb_ics_do`
--
ALTER TABLE `tb_ics_do`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kd_faktur_do` (`kd_faktur`),
  ADD KEY `fk_nama_barang_do` (`nama_barang`(255)),
  ADD KEY `fk_exp_date_do` (`exp_date`(255));

--
-- Indeks untuk tabel `tb_ics_opname`
--
ALTER TABLE `tb_ics_opname`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_expired_date` (`exp_date`(255)),
  ADD KEY `fk_nmbarang` (`nama_barang`(255)) USING BTREE,
  ADD KEY `fk_kdsystem` (`kd_system`) USING BTREE;

--
-- Indeks untuk tabel `tb_ics_po`
--
ALTER TABLE `tb_ics_po`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_nama_barang_po` (`nama_barang`(255)),
  ADD KEY `fk_exp_date` (`exp_date`(255)),
  ADD KEY `fk_kd_lpb` (`kd_faktur_lpb`);

--
-- Indeks untuk tabel `tb_ics_supp`
--
ALTER TABLE `tb_ics_supp`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_indikator_hows`
--
ALTER TABLE `tb_indikator_hows`
  ADD PRIMARY KEY (`id_indikator`),
  ADD KEY `idx_id_how` (`id_how`),
  ADD KEY `idx_urutan` (`urutan`);

--
-- Indeks untuk tabel `tb_indikator_whats`
--
ALTER TABLE `tb_indikator_whats`
  ADD PRIMARY KEY (`id_indikator`),
  ADD KEY `id_what` (`id_what`);

--
-- Indeks untuk tabel `tb_issue`
--
ALTER TABLE `tb_issue`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_jobdesk`
--
ALTER TABLE `tb_jobdesk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_karyawan`
--
ALTER TABLE `tb_karyawan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_nik` (`nik`);

--
-- Indeks untuk tabel `tb_karyawan_keluarmasuk`
--
ALTER TABLE `tb_karyawan_keluarmasuk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_kasbon`
--
ALTER TABLE `tb_kasbon`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_kd_system_stock`
--
ALTER TABLE `tb_kd_system_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kdsystem` (`kd_system`);

--
-- Indeks untuk tabel `tb_kpi`
--
ALTER TABLE `tb_kpi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_kpi_history`
--
ALTER TABLE `tb_kpi_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_kpi_month` (`id_user`,`id_kpi`,`bulan`),
  ADD KEY `idx_summary` (`id_user`,`bulan`,`is_summary`);

--
-- Indeks untuk tabel `tb_kpi_lock_settings`
--
ALTER TABLE `tb_kpi_lock_settings`
  ADD PRIMARY KEY (`id_lock`),
  ADD KEY `idx_tanggal` (`tanggal_mulai`,`tanggal_selesai`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `tb_kpi_reset_log`
--
ALTER TABLE `tb_kpi_reset_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bulan` (`bulan`,`tahun`),
  ADD KEY `reset_by` (`reset_by`);

--
-- Indeks untuk tabel `tb_kpi_verified`
--
ALTER TABLE `tb_kpi_verified`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_verified` (`id_user`,`bulan`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indeks untuk tabel `tb_lap_distribusi`
--
ALTER TABLE `tb_lap_distribusi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_loading_kk`
--
ALTER TABLE `tb_loading_kk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tgl` (`tgl`),
  ADD KEY `idx_is_archived` (`is_archived`);

--
-- Indeks untuk tabel `tb_loading_lk`
--
ALTER TABLE `tb_loading_lk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tgl` (`tgl`),
  ADD KEY `idx_is_archived` (`is_archived`);

--
-- Indeks untuk tabel `tb_login_log`
--
ALTER TABLE `tb_login_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_log_confirm_sales`
--
ALTER TABLE `tb_log_confirm_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_confirm_sales_kd_do_confirm_at` (`kd_do`,`confirm_at`,`id`);

--
-- Indeks untuk tabel `tb_log_do`
--
ALTER TABLE `tb_log_do`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_do` (`kd_do`(255)),
  ADD KEY `fk_inputer` (`inputer`(255)),
  ADD KEY `fk_tglinputer` (`tgl_input`(255));

--
-- Indeks untuk tabel `tb_log_ics`
--
ALTER TABLE `tb_log_ics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kdbarang` (`nama_barang`(768));

--
-- Indeks untuk tabel `tb_log_mutasi`
--
ALTER TABLE `tb_log_mutasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_lpb`
--
ALTER TABLE `tb_lpb`
  ADD PRIMARY KEY (`id_lpb`);

--
-- Indeks untuk tabel `tb_lpb_batch`
--
ALTER TABLE `tb_lpb_batch`
  ADD PRIMARY KEY (`id_batch`);

--
-- Indeks untuk tabel `tb_lpb_detail`
--
ALTER TABLE `tb_lpb_detail`
  ADD PRIMARY KEY (`id_detail_lpb`),
  ADD KEY `idx_lpb_detail_lpb_barang` (`id_lpb`,`kd_barang`);

--
-- Indeks untuk tabel `tb_lpb_log`
--
ALTER TABLE `tb_lpb_log`
  ADD PRIMARY KEY (`id_log`);

--
-- Indeks untuk tabel `tb_lpb_price_adjustment`
--
ALTER TABLE `tb_lpb_price_adjustment`
  ADD PRIMARY KEY (`id_adjustment`),
  ADD UNIQUE KEY `uk_no_adjustment` (`no_adjustment`),
  ADD KEY `idx_lpb_salah` (`id_lpb_salah`),
  ADD KEY `idx_lpb_adjustment` (`id_lpb_adjustment`),
  ADD KEY `idx_retur` (`id_retur_pembelian`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `tb_lpb_price_adjustment_detail`
--
ALTER TABLE `tb_lpb_price_adjustment_detail`
  ADD PRIMARY KEY (`id_adjustment_detail`),
  ADD KEY `idx_adjustment` (`id_adjustment`),
  ADD KEY `idx_detail_salah` (`id_detail_lpb_salah`),
  ADD KEY `idx_detail_adjustment` (`id_detail_lpb_adjustment`),
  ADD KEY `idx_barang_batch` (`kd_barang`,`no_lot_adjustment`,`expired_date_adjustment`);

--
-- Indeks untuk tabel `tb_master_barang`
--
ALTER TABLE `tb_master_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_barang` (`kode_barang`),
  ADD KEY `fk_suplier` (`kd_suplier`),
  ADD KEY `fk_nmbarang` (`nm_barang`),
  ADD KEY `fk_kdsystem` (`kd_system`);

--
-- Indeks untuk tabel `tb_master_barang_all`
--
ALTER TABLE `tb_master_barang_all`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_barang` (`kd_barang`),
  ADD KEY `fk_nmbarang` (`nama_barang`(768)),
  ADD KEY `fk_suplier` (`kd_supplier`),
  ADD KEY `idx_gudang_wilayah` (`id_gudang`,`id_wilayah`),
  ADD KEY `idx_barang_kd_barang` (`kd_barang`),
  ADD KEY `idx_barang` (`kd_barang`);

--
-- Indeks untuk tabel `tb_mbarang`
--
ALTER TABLE `tb_mbarang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_nmbarang` (`nm_barang`),
  ADD KEY `fk_kdbarang` (`kd_barang`);

--
-- Indeks untuk tabel `tb_menu`
--
ALTER TABLE `tb_menu`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_mutasi`
--
ALTER TABLE `tb_mutasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_notifikasi`
--
ALTER TABLE `tb_notifikasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_op_driver`
--
ALTER TABLE `tb_op_driver`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_op_helper`
--
ALTER TABLE `tb_op_helper`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_op_plat`
--
ALTER TABLE `tb_op_plat`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_order_tracking_driver`
--
ALTER TABLE `tb_order_tracking_driver`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_pemenang`
--
ALTER TABLE `tb_pemenang`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_pengajuan_od`
--
ALTER TABLE `tb_pengajuan_od`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_pengajuan_od_faktur`
--
ALTER TABLE `tb_pengajuan_od_faktur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pengajuan` (`id_pengajuan`);

--
-- Indeks untuk tabel `tb_penilaian_karakter_assignment`
--
ALTER TABLE `tb_penilaian_karakter_assignment`
  ADD PRIMARY KEY (`id_assignment`),
  ADD UNIQUE KEY `unique_karakter_assignment` (`id_user_dinilai`,`id_penilai`,`bulan`),
  ADD KEY `idx_karakter_assignment_penilai` (`id_penilai`,`status`,`bulan`),
  ADD KEY `idx_karakter_assignment_atasan` (`id_atasan`,`status`,`bulan`);

--
-- Indeks untuk tabel `tb_penilaian_karakter_response`
--
ALTER TABLE `tb_penilaian_karakter_response`
  ADD PRIMARY KEY (`id_response`),
  ADD UNIQUE KEY `unique_karakter_response_month` (`id_assignment`,`bulan`),
  ADD KEY `idx_karakter_response_bulan` (`bulan`,`submitted_at`);

--
-- Indeks untuk tabel `tb_pnd_do`
--
ALTER TABLE `tb_pnd_do`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_customer` (`kd_customer`),
  ADD KEY `fk_faktur` (`kd_faktur`),
  ADD KEY `kd_barang` (`kd_barang`),
  ADD KEY `fk_do` (`kd_do`);

--
-- Indeks untuk tabel `tb_po_pending`
--
ALTER TABLE `tb_po_pending`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sup` (`kd_sup`),
  ADD KEY `fk_barang` (`kd_barang`);

--
-- Indeks untuk tabel `tb_po_received`
--
ALTER TABLE `tb_po_received`
  ADD PRIMARY KEY (`id_detail_lpb`),
  ADD KEY `idx_no_po` (`no_po`),
  ADD KEY `idx_kd_barang` (`kd_barang`);

--
-- Indeks untuk tabel `tb_pre_do`
--
ALTER TABLE `tb_pre_do`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_customer` (`kd_customer`),
  ADD KEY `fk_faktur` (`kd_faktur`),
  ADD KEY `kd_barang` (`kd_barang`),
  ADD KEY `fk_nmbarang` (`nama_barang`(255)),
  ADD KEY `idx_pre_do_kd_rute` (`kd_rute`(768)),
  ADD KEY `idx_pre_do_kd_barang` (`kd_barang`),
  ADD KEY `idx_pre_do_data_sts` (`data_sts`),
  ADD KEY `idx_pre_do_delivery_faktur` (`delivery_at`,`kd_faktur`),
  ADD KEY `idx_pre_do_sts_faktur` (`data_sts`,`kd_faktur`),
  ADD KEY `idx_pre_do_barang` (`kd_barang`),
  ADD KEY `idx_pre_do_customer` (`kd_customer`),
  ADD KEY `idx_pre_do_rute` (`kd_rute`(768));

--
-- Indeks untuk tabel `tb_pre_po`
--
ALTER TABLE `tb_pre_po`
  ADD PRIMARY KEY (`id_pre_po`);

--
-- Indeks untuk tabel `tb_pre_po_adjustment_log`
--
ALTER TABLE `tb_pre_po_adjustment_log`
  ADD PRIMARY KEY (`id_log`);

--
-- Indeks untuk tabel `tb_pre_po_diskon_history`
--
ALTER TABLE `tb_pre_po_diskon_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kd_po` (`kd_po`),
  ADD KEY `idx_id_diskon_source` (`id_diskon_source`);

--
-- Indeks untuk tabel `tb_pre_po_invoice_adjustment`
--
ALTER TABLE `tb_pre_po_invoice_adjustment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_kd_po_barang` (`kd_po`,`kd_barang`),
  ADD KEY `idx_kd_po` (`kd_po`);

--
-- Indeks untuk tabel `tb_qty_lot`
--
ALTER TABLE `tb_qty_lot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_barang` (`kd_barang`),
  ADD KEY `fk_nmbarang` (`nm_barang`),
  ADD KEY `fk_lot` (`no_lot`(768)),
  ADD KEY `fk_expdate` (`exp_date`(768)),
  ADD KEY `fk_suplier` (`suplier`);

--
-- Indeks untuk tabel `tb_req_opname`
--
ALTER TABLE `tb_req_opname`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_retur_barang`
--
ALTER TABLE `tb_retur_barang`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_retur_pembelian`
--
ALTER TABLE `tb_retur_pembelian`
  ADD PRIMARY KEY (`id_retur_pembelian`),
  ADD UNIQUE KEY `uk_no_retur_pembelian` (`no_retur_pembelian`),
  ADD KEY `idx_lpb` (`id_lpb`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_jurnal` (`id_jurnal`);

--
-- Indeks untuk tabel `tb_retur_pembelian_detail`
--
ALTER TABLE `tb_retur_pembelian_detail`
  ADD PRIMARY KEY (`id_detail_retur_pembelian`),
  ADD KEY `idx_retur` (`id_retur_pembelian`),
  ADD KEY `idx_detail_lpb` (`id_detail_lpb`),
  ADD KEY `idx_barang_batch` (`kd_barang`,`no_lot`,`expired_date`);

--
-- Indeks untuk tabel `tb_retur_pembelian_log`
--
ALTER TABLE `tb_retur_pembelian_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_retur_log` (`id_retur_pembelian`),
  ADD KEY `idx_action` (`action_type`);

--
-- Indeks untuk tabel `tb_rutecs`
--
ALTER TABLE `tb_rutecs`
  ADD PRIMARY KEY (`id_rute`),
  ADD KEY `idx_rute` (`kd_rute`(768));

--
-- Indeks untuk tabel `tb_saldo_awal`
--
ALTER TABLE `tb_saldo_awal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_saldo_awal_barang` (`wilayah_id`,`kode_barang_system`,`exp_date`(30));

--
-- Indeks untuk tabel `tb_satuan`
--
ALTER TABLE `tb_satuan`
  ADD PRIMARY KEY (`id_satuan`);

--
-- Indeks untuk tabel `tb_schedule_dirut`
--
ALTER TABLE `tb_schedule_dirut`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_service_truk`
--
ALTER TABLE `tb_service_truk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_set_tax`
--
ALTER TABLE `tb_set_tax`
  ADD PRIMARY KEY (`id_tax`);

--
-- Indeks untuk tabel `tb_sop`
--
ALTER TABLE `tb_sop`
  ADD PRIMARY KEY (`id_sop`);

--
-- Indeks untuk tabel `tb_ss`
--
ALTER TABLE `tb_ss`
  ADD PRIMARY KEY (`id_poinss`);

--
-- Indeks untuk tabel `tb_sspoin`
--
ALTER TABLE `tb_sspoin`
  ADD PRIMARY KEY (`id_sspoin`);

--
-- Indeks untuk tabel `tb_ss_history`
--
ALTER TABLE `tb_ss_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_ss_history_month` (`id_user`,`id_sspoin`,`bulan`),
  ADD KEY `idx_ss_history_user_month` (`id_user`,`bulan`),
  ADD KEY `idx_ss_history_category` (`id_user`,`id_ss`,`bulan`);

--
-- Indeks untuk tabel `tb_ss_simulasi`
--
ALTER TABLE `tb_ss_simulasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_simulasi_user_sspoin` (`id_user`,`id_sspoin`),
  ADD KEY `idx_simulasi_user_category` (`id_user`,`id_ss`);

--
-- Indeks untuk tabel `tb_ss_verified`
--
ALTER TABLE `tb_ss_verified`
  ADD PRIMARY KEY (`id_ss_verified`),
  ADD UNIQUE KEY `unique_ss_user_bulan` (`id_user`,`bulan`);

--
-- Indeks untuk tabel `tb_stock_hold`
--
ALTER TABLE `tb_stock_hold`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_stock_status`
--
ALTER TABLE `tb_stock_status`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_suplier`
--
ALTER TABLE `tb_suplier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_suplier` (`kd_suplier`);

--
-- Indeks untuk tabel `tb_surat_peringatan`
--
ALTER TABLE `tb_surat_peringatan`
  ADD PRIMARY KEY (`id_sp`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `status` (`status`);

--
-- Indeks untuk tabel `tb_tamu`
--
ALTER TABLE `tb_tamu`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_tamu_lby`
--
ALTER TABLE `tb_tamu_lby`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_terima_paket`
--
ALTER TABLE `tb_terima_paket`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_tim`
--
ALTER TABLE `tb_tim`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_tmp_detaildo`
--
ALTER TABLE `tb_tmp_detaildo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_customer` (`kd_customer`),
  ADD KEY `fk_faktur` (`kd_faktur`),
  ADD KEY `kd_barang` (`kd_barang`),
  ADD KEY `fk_do` (`kd_do`);

--
-- Indeks untuk tabel `tb_tmp_do`
--
ALTER TABLE `tb_tmp_do`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_faktur` (`kd_faktur`);

--
-- Indeks untuk tabel `tb_tmp_lap_distribusi`
--
ALTER TABLE `tb_tmp_lap_distribusi`
  ADD PRIMARY KEY (`id_lap_dis`);

--
-- Indeks untuk tabel `tb_tmp_mutasi`
--
ALTER TABLE `tb_tmp_mutasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_inputer`),
  ADD KEY `idx_barang_exp` (`nama_barang`(255),`exp_date`(255)) USING BTREE;

--
-- Indeks untuk tabel `tb_truck`
--
ALTER TABLE `tb_truck`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_truk`
--
ALTER TABLE `tb_truk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_users`
--
ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tb_users_username` (`username`),
  ADD KEY `idx_tb_users_jobdesk_hrd` (`jobdesk_hrd`),
  ADD KEY `idx_tb_users_status` (`status`);

--
-- Indeks untuk tabel `tb_user_level_mapping`
--
ALTER TABLE `tb_user_level_mapping`
  ADD PRIMARY KEY (`id_mapping`),
  ADD UNIQUE KEY `jabatan` (`jabatan`);

--
-- Indeks untuk tabel `tb_whats`
--
ALTER TABLE `tb_whats`
  ADD PRIMARY KEY (`id_what`);

--
-- Indeks untuk tabel `tb_wilayah`
--
ALTER TABLE `tb_wilayah`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `trashbin_do`
--
ALTER TABLE `trashbin_do`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_customer` (`kd_customer`),
  ADD KEY `fk_faktur` (`kd_faktur`),
  ADD KEY `kd_barang` (`kd_barang`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cctv_tracking`
--
ALTER TABLE `cctv_tracking`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stockopname_master`
--
ALTER TABLE `stockopname_master`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stockopname_master_box`
--
ALTER TABLE `stockopname_master_box`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stockopname_master_item`
--
ALTER TABLE `stockopname_master_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stockopname_master_manual_item`
--
ALTER TABLE `stockopname_master_manual_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stockopname_opname`
--
ALTER TABLE `stockopname_opname`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stockopname_opname_log`
--
ALTER TABLE `stockopname_opname_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stockopname_opname_manual`
--
ALTER TABLE `stockopname_opname_manual`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stockopname_pending`
--
ALTER TABLE `stockopname_pending`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stockopname_recyclebin_input`
--
ALTER TABLE `stockopname_recyclebin_input`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbar_archive`
--
ALTER TABLE `tbar_archive`
  MODIFY `id_archive` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbar_bobotkpi`
--
ALTER TABLE `tbar_bobotkpi`
  MODIFY `idbobotkpi` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbar_hows`
--
ALTER TABLE `tbar_hows`
  MODIFY `id_how` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbar_indikator_hows`
--
ALTER TABLE `tbar_indikator_hows`
  MODIFY `id_indikator` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbar_indikator_whats`
--
ALTER TABLE `tbar_indikator_whats`
  MODIFY `id_indikator` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbar_kpi`
--
ALTER TABLE `tbar_kpi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbar_sp_archive`
--
ALTER TABLE `tbar_sp_archive`
  MODIFY `id_sp_archive` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbar_whats`
--
ALTER TABLE `tbar_whats`
  MODIFY `id_what` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tberp_stock_batch`
--
ALTER TABLE `tberp_stock_batch`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tberp_stock_ledger`
--
ALTER TABLE `tberp_stock_ledger`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbhrd_environment_issues`
--
ALTER TABLE `tbhrd_environment_issues`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbhrd_issue_evidences`
--
ALTER TABLE `tbhrd_issue_evidences`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbhrd_issue_logs`
--
ALTER TABLE `tbhrd_issue_logs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbhrd_issue_rating`
--
ALTER TABLE `tbhrd_issue_rating`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbhrd_issue_status`
--
ALTER TABLE `tbhrd_issue_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbhrd_lokasi`
--
ALTER TABLE `tbhrd_lokasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbhrd_nilai_lingkungan`
--
ALTER TABLE `tbhrd_nilai_lingkungan`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_akun`
--
ALTER TABLE `tbkeu_akun`
  MODIFY `id_akun` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_akun_karismaerp_ref`
--
ALTER TABLE `tbkeu_akun_karismaerp_ref`
  MODIFY `id_ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_dummy_source`
--
ALTER TABLE `tbkeu_dummy_source`
  MODIFY `id_dummy` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_jenis_jurnal`
--
ALTER TABLE `tbkeu_jenis_jurnal`
  MODIFY `id_jenis_jurnal` smallint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_jurnal`
--
ALTER TABLE `tbkeu_jurnal`
  MODIFY `id_jurnal` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_jurnal_detail`
--
ALTER TABLE `tbkeu_jurnal_detail`
  MODIFY `id_jurnal_detail` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_jurnal_log`
--
ALTER TABLE `tbkeu_jurnal_log`
  MODIFY `id_log` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_karismaerp_import_batch`
--
ALTER TABLE `tbkeu_karismaerp_import_batch`
  MODIFY `id_batch` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_karismaerp_kelompok_jurnal`
--
ALTER TABLE `tbkeu_karismaerp_kelompok_jurnal`
  MODIFY `id_kelompok_karismaerp` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_kas_keluar`
--
ALTER TABLE `tbkeu_kas_keluar`
  MODIFY `id_kas_keluar` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_kas_keluar_detail`
--
ALTER TABLE `tbkeu_kas_keluar_detail`
  MODIFY `id_kas_keluar_detail` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_kas_masuk`
--
ALTER TABLE `tbkeu_kas_masuk`
  MODIFY `id_kas_masuk` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_kas_masuk_detail`
--
ALTER TABLE `tbkeu_kas_masuk_detail`
  MODIFY `id_kas_masuk_detail` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_mapping_akun`
--
ALTER TABLE `tbkeu_mapping_akun`
  MODIFY `id_mapping` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_nomor_dokumen`
--
ALTER TABLE `tbkeu_nomor_dokumen`
  MODIFY `id_nomor` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_pembayaran`
--
ALTER TABLE `tbkeu_pembayaran`
  MODIFY `id_pembayaran` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_pembayaran_alokasi`
--
ALTER TABLE `tbkeu_pembayaran_alokasi`
  MODIFY `id_alokasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_pembayaran_faktur`
--
ALTER TABLE `tbkeu_pembayaran_faktur`
  MODIFY `id_pembayaran` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_periode_fiskal`
--
ALTER TABLE `tbkeu_periode_fiskal`
  MODIFY `id_periode` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_periode_fiskal_log`
--
ALTER TABLE `tbkeu_periode_fiskal_log`
  MODIFY `id_log` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_posting_exception`
--
ALTER TABLE `tbkeu_posting_exception`
  MODIFY `id_exception` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_report_rule_akun`
--
ALTER TABLE `tbkeu_report_rule_akun`
  MODIFY `id_rule` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_saldo_awal_akun`
--
ALTER TABLE `tbkeu_saldo_awal_akun`
  MODIFY `id_saldo_awal` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkeu_sub_klasifikasi_akun`
--
ALTER TABLE `tbkeu_sub_klasifikasi_akun`
  MODIFY `id_sub_klasifikasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_dca`
--
ALTER TABLE `tbkmt_dca`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_dca_detail`
--
ALTER TABLE `tbkmt_dca_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_dca_kegiatan`
--
ALTER TABLE `tbkmt_dca_kegiatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_dca_verifikasi_log`
--
ALTER TABLE `tbkmt_dca_verifikasi_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_gaji`
--
ALTER TABLE `tbkmt_gaji`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_omset`
--
ALTER TABLE `tbkmt_omset`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_operasional`
--
ALTER TABLE `tbkmt_operasional`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_operasional_verifikasi_log`
--
ALTER TABLE `tbkmt_operasional_verifikasi_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_others`
--
ALTER TABLE `tbkmt_others`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_promo_material`
--
ALTER TABLE `tbkmt_promo_material`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_retur`
--
ALTER TABLE `tbkmt_retur`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbkmt_wilayah`
--
ALTER TABLE `tbkmt_wilayah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_akun_tr`
--
ALTER TABLE `tbpo_akun_tr`
  MODIFY `id_akun` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_barang`
--
ALTER TABLE `tbpo_barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_barang_nk`
--
ALTER TABLE `tbpo_barang_nk`
  MODIFY `id_brg_nk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_barang_nk_lokasi`
--
ALTER TABLE `tbpo_barang_nk_lokasi`
  MODIFY `id_lokasi` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_barang_packaging`
--
ALTER TABLE `tbpo_barang_packaging`
  MODIFY `id_packaging` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_detail_po`
--
ALTER TABLE `tbpo_detail_po`
  MODIFY `id_det_po` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_detail_po_nk`
--
ALTER TABLE `tbpo_detail_po_nk`
  MODIFY `id_det_po_nk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_detail_req`
--
ALTER TABLE `tbpo_detail_req`
  MODIFY `id_det_po_nk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_diskon`
--
ALTER TABLE `tbpo_diskon`
  MODIFY `id_diskon` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_diskon_merk`
--
ALTER TABLE `tbpo_diskon_merk`
  MODIFY `id_diskon` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_file_bukti_beli`
--
ALTER TABLE `tbpo_file_bukti_beli`
  MODIFY `id_fk_bukti` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_file_nk`
--
ALTER TABLE `tbpo_file_nk`
  MODIFY `id_file_nk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_formula`
--
ALTER TABLE `tbpo_formula`
  MODIFY `id_formula` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_formula_result`
--
ALTER TABLE `tbpo_formula_result`
  MODIFY `id_result` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_formula_variable`
--
ALTER TABLE `tbpo_formula_variable`
  MODIFY `id_variable` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_generateqrcode`
--
ALTER TABLE `tbpo_generateqrcode`
  MODIFY `id_gqrcode` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_generate_kd`
--
ALTER TABLE `tbpo_generate_kd`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_generate_kd_ponk`
--
ALTER TABLE `tbpo_generate_kd_ponk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_kat_br`
--
ALTER TABLE `tbpo_kat_br`
  MODIFY `id_kat_br` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_notetemplate`
--
ALTER TABLE `tbpo_notetemplate`
  MODIFY `id_nt_template` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_note_barang`
--
ALTER TABLE `tbpo_note_barang`
  MODIFY `id_nt_barang` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_note_direktur`
--
ALTER TABLE `tbpo_note_direktur`
  MODIFY `id_note` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_note_pembelian`
--
ALTER TABLE `tbpo_note_pembelian`
  MODIFY `id_nt_pembelian` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_nt_tmp_pembelian`
--
ALTER TABLE `tbpo_nt_tmp_pembelian`
  MODIFY `id_tmp_nt_pembelian` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_po`
--
ALTER TABLE `tbpo_po`
  MODIFY `id_po` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_po_nk`
--
ALTER TABLE `tbpo_po_nk`
  MODIFY `id_po_nk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_ratings`
--
ALTER TABLE `tbpo_ratings`
  MODIFY `id_rating` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_req_masterbarang`
--
ALTER TABLE `tbpo_req_masterbarang`
  MODIFY `id_reqmbarang` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_req_nk`
--
ALTER TABLE `tbpo_req_nk`
  MODIFY `id_po_nk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_satuan`
--
ALTER TABLE `tbpo_satuan`
  MODIFY `id_satuan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_set_note`
--
ALTER TABLE `tbpo_set_note`
  MODIFY `id_set_note` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_set_tax`
--
ALTER TABLE `tbpo_set_tax`
  MODIFY `id_tax` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_sosialisasi`
--
ALTER TABLE `tbpo_sosialisasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_suplier`
--
ALTER TABLE `tbpo_suplier`
  MODIFY `id_suplier` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_tmp_diskon`
--
ALTER TABLE `tbpo_tmp_diskon`
  MODIFY `id_tmp_diskon` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_tmp_item`
--
ALTER TABLE `tbpo_tmp_item`
  MODIFY `id_tmp` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_tmp_item_nk`
--
ALTER TABLE `tbpo_tmp_item_nk`
  MODIFY `id_tmp_nk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_tmp_note_barang`
--
ALTER TABLE `tbpo_tmp_note_barang`
  MODIFY `id_nt_tmp_barang` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_tmp_tax`
--
ALTER TABLE `tbpo_tmp_tax`
  MODIFY `id_tmp_tax` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_tracking_po`
--
ALTER TABLE `tbpo_tracking_po`
  MODIFY `id_po_tracking` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_transaksi`
--
ALTER TABLE `tbpo_transaksi`
  MODIFY `id_transnk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_transaksi_tmp`
--
ALTER TABLE `tbpo_transaksi_tmp`
  MODIFY `id_transnk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_transaksi_trashbin`
--
ALTER TABLE `tbpo_transaksi_trashbin`
  MODIFY `id_trashbin` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbpo_user`
--
ALTER TABLE `tbpo_user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbq_module`
--
ALTER TABLE `tbq_module`
  MODIFY `id_qmodule` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbq_nilaim`
--
ALTER TABLE `tbq_nilaim`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbq_review_pic`
--
ALTER TABLE `tbq_review_pic`
  MODIFY `id_review` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbq_review_q`
--
ALTER TABLE `tbq_review_q`
  MODIFY `id_reviewq` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbrp_activity_log`
--
ALTER TABLE `tbrp_activity_log`
  MODIFY `id_log` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbrp_retur_penjualan_detail`
--
ALTER TABLE `tbrp_retur_penjualan_detail`
  MODIFY `id_retur_detail` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbrp_retur_penjualan_header`
--
ALTER TABLE `tbrp_retur_penjualan_header`
  MODIFY `id_retur` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbrp_spr_detail`
--
ALTER TABLE `tbrp_spr_detail`
  MODIFY `id_spr_detail` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbrp_spr_header`
--
ALTER TABLE `tbrp_spr_header`
  MODIFY `id_spr` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbsim_bobotkpi`
--
ALTER TABLE `tbsim_bobotkpi`
  MODIFY `idbobotkpi` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbsim_hows`
--
ALTER TABLE `tbsim_hows`
  MODIFY `id_how` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbsim_indikator_hows`
--
ALTER TABLE `tbsim_indikator_hows`
  MODIFY `id_indikator` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbsim_indikator_whats`
--
ALTER TABLE `tbsim_indikator_whats`
  MODIFY `id_indikator` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbsim_kpi`
--
ALTER TABLE `tbsim_kpi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbsim_whats`
--
ALTER TABLE `tbsim_whats`
  MODIFY `id_what` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbso_activity_log`
--
ALTER TABLE `tbso_activity_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbso_approval_harga`
--
ALTER TABLE `tbso_approval_harga`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbso_cancel_partial_request`
--
ALTER TABLE `tbso_cancel_partial_request`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbso_faktur_detail`
--
ALTER TABLE `tbso_faktur_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbso_faktur_jurnal`
--
ALTER TABLE `tbso_faktur_jurnal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbso_faktur_log`
--
ALTER TABLE `tbso_faktur_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbso_faktur_penjualan`
--
ALTER TABLE `tbso_faktur_penjualan`
  MODIFY `id_faktur` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbso_sales_order`
--
ALTER TABLE `tbso_sales_order`
  MODIFY `id_so` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbso_sales_order_detail`
--
ALTER TABLE `tbso_sales_order_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_akses_level`
--
ALTER TABLE `tb_akses_level`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_akses_menu`
--
ALTER TABLE `tb_akses_menu`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_auth`
--
ALTER TABLE `tb_auth`
  MODIFY `id_auth` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_barangv2`
--
ALTER TABLE `tb_barangv2`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_bobotkpi`
--
ALTER TABLE `tb_bobotkpi`
  MODIFY `idbobotkpi` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_bongkaran`
--
ALTER TABLE `tb_bongkaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_bongkaran_checker`
--
ALTER TABLE `tb_bongkaran_checker`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_checklist_kendaraan`
--
ALTER TABLE `tb_checklist_kendaraan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_checklist_kendaraan_detail`
--
ALTER TABLE `tb_checklist_kendaraan_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_checkup_mekanik_kategori`
--
ALTER TABLE `tb_checkup_mekanik_kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_checkup_mekanik_kategori_detail`
--
ALTER TABLE `tb_checkup_mekanik_kategori_detail`
  MODIFY `id_detail_kat` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_checkup_mekanik_kategori_foto`
--
ALTER TABLE `tb_checkup_mekanik_kategori_foto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_customer`
--
ALTER TABLE `tb_customer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_customer_list_undian`
--
ALTER TABLE `tb_customer_list_undian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_dailystock`
--
ALTER TABLE `tb_dailystock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_dailystock_global`
--
ALTER TABLE `tb_dailystock_global`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_departemen`
--
ALTER TABLE `tb_departemen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_detail_do`
--
ALTER TABLE `tb_detail_do`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_detail_mutasi`
--
ALTER TABLE `tb_detail_mutasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_detail_retur_barang`
--
ALTER TABLE `tb_detail_retur_barang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_det_tracking_driver`
--
ALTER TABLE `tb_det_tracking_driver`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_do`
--
ALTER TABLE `tb_do`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_editlog_faktur`
--
ALTER TABLE `tb_editlog_faktur`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_eviden`
--
ALTER TABLE `tb_eviden`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_eviden_backup`
--
ALTER TABLE `tb_eviden_backup`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_expedisi`
--
ALTER TABLE `tb_expedisi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_feedback`
--
ALTER TABLE `tb_feedback`
  MODIFY `id_feedback` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_gbrupload_cheklist`
--
ALTER TABLE `tb_gbrupload_cheklist`
  MODIFY `id_upload` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_gudang`
--
ALTER TABLE `tb_gudang`
  MODIFY `id_gudang` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_gudang_wilayah`
--
ALTER TABLE `tb_gudang_wilayah`
  MODIFY `id_wilayah` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_hows`
--
ALTER TABLE `tb_hows`
  MODIFY `id_how` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_ics`
--
ALTER TABLE `tb_ics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_ics_do`
--
ALTER TABLE `tb_ics_do`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_ics_opname`
--
ALTER TABLE `tb_ics_opname`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_ics_po`
--
ALTER TABLE `tb_ics_po`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_ics_supp`
--
ALTER TABLE `tb_ics_supp`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_indikator_hows`
--
ALTER TABLE `tb_indikator_hows`
  MODIFY `id_indikator` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_indikator_whats`
--
ALTER TABLE `tb_indikator_whats`
  MODIFY `id_indikator` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_issue`
--
ALTER TABLE `tb_issue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_jobdesk`
--
ALTER TABLE `tb_jobdesk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_karyawan`
--
ALTER TABLE `tb_karyawan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_karyawan_keluarmasuk`
--
ALTER TABLE `tb_karyawan_keluarmasuk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_kasbon`
--
ALTER TABLE `tb_kasbon`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_kd_system_stock`
--
ALTER TABLE `tb_kd_system_stock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_kpi`
--
ALTER TABLE `tb_kpi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_kpi_history`
--
ALTER TABLE `tb_kpi_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_kpi_lock_settings`
--
ALTER TABLE `tb_kpi_lock_settings`
  MODIFY `id_lock` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_kpi_reset_log`
--
ALTER TABLE `tb_kpi_reset_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_kpi_verified`
--
ALTER TABLE `tb_kpi_verified`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_lap_distribusi`
--
ALTER TABLE `tb_lap_distribusi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_loading_kk`
--
ALTER TABLE `tb_loading_kk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_loading_lk`
--
ALTER TABLE `tb_loading_lk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_login_log`
--
ALTER TABLE `tb_login_log`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_log_confirm_sales`
--
ALTER TABLE `tb_log_confirm_sales`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_log_do`
--
ALTER TABLE `tb_log_do`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_log_ics`
--
ALTER TABLE `tb_log_ics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_log_mutasi`
--
ALTER TABLE `tb_log_mutasi`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_lpb`
--
ALTER TABLE `tb_lpb`
  MODIFY `id_lpb` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_lpb_batch`
--
ALTER TABLE `tb_lpb_batch`
  MODIFY `id_batch` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_lpb_detail`
--
ALTER TABLE `tb_lpb_detail`
  MODIFY `id_detail_lpb` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_lpb_log`
--
ALTER TABLE `tb_lpb_log`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_lpb_price_adjustment`
--
ALTER TABLE `tb_lpb_price_adjustment`
  MODIFY `id_adjustment` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_lpb_price_adjustment_detail`
--
ALTER TABLE `tb_lpb_price_adjustment_detail`
  MODIFY `id_adjustment_detail` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_master_barang`
--
ALTER TABLE `tb_master_barang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_master_barang_all`
--
ALTER TABLE `tb_master_barang_all`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_mbarang`
--
ALTER TABLE `tb_mbarang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_menu`
--
ALTER TABLE `tb_menu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_mutasi`
--
ALTER TABLE `tb_mutasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_notifikasi`
--
ALTER TABLE `tb_notifikasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_op_driver`
--
ALTER TABLE `tb_op_driver`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_op_helper`
--
ALTER TABLE `tb_op_helper`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_op_plat`
--
ALTER TABLE `tb_op_plat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_order_tracking_driver`
--
ALTER TABLE `tb_order_tracking_driver`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_pemenang`
--
ALTER TABLE `tb_pemenang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_pengajuan_od`
--
ALTER TABLE `tb_pengajuan_od`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_pengajuan_od_faktur`
--
ALTER TABLE `tb_pengajuan_od_faktur`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_penilaian_karakter_assignment`
--
ALTER TABLE `tb_penilaian_karakter_assignment`
  MODIFY `id_assignment` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_penilaian_karakter_response`
--
ALTER TABLE `tb_penilaian_karakter_response`
  MODIFY `id_response` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_pnd_do`
--
ALTER TABLE `tb_pnd_do`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_po_pending`
--
ALTER TABLE `tb_po_pending`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_po_received`
--
ALTER TABLE `tb_po_received`
  MODIFY `id_detail_lpb` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_pre_do`
--
ALTER TABLE `tb_pre_do`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_pre_po`
--
ALTER TABLE `tb_pre_po`
  MODIFY `id_pre_po` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_pre_po_adjustment_log`
--
ALTER TABLE `tb_pre_po_adjustment_log`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_pre_po_diskon_history`
--
ALTER TABLE `tb_pre_po_diskon_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_pre_po_invoice_adjustment`
--
ALTER TABLE `tb_pre_po_invoice_adjustment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_qty_lot`
--
ALTER TABLE `tb_qty_lot`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_req_opname`
--
ALTER TABLE `tb_req_opname`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_retur_barang`
--
ALTER TABLE `tb_retur_barang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_retur_pembelian`
--
ALTER TABLE `tb_retur_pembelian`
  MODIFY `id_retur_pembelian` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_retur_pembelian_detail`
--
ALTER TABLE `tb_retur_pembelian_detail`
  MODIFY `id_detail_retur_pembelian` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_retur_pembelian_log`
--
ALTER TABLE `tb_retur_pembelian_log`
  MODIFY `id_log` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_rutecs`
--
ALTER TABLE `tb_rutecs`
  MODIFY `id_rute` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_saldo_awal`
--
ALTER TABLE `tb_saldo_awal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_satuan`
--
ALTER TABLE `tb_satuan`
  MODIFY `id_satuan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_schedule_dirut`
--
ALTER TABLE `tb_schedule_dirut`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_service_truk`
--
ALTER TABLE `tb_service_truk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_set_tax`
--
ALTER TABLE `tb_set_tax`
  MODIFY `id_tax` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_sop`
--
ALTER TABLE `tb_sop`
  MODIFY `id_sop` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_ss`
--
ALTER TABLE `tb_ss`
  MODIFY `id_poinss` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_sspoin`
--
ALTER TABLE `tb_sspoin`
  MODIFY `id_sspoin` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_ss_history`
--
ALTER TABLE `tb_ss_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_ss_simulasi`
--
ALTER TABLE `tb_ss_simulasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_ss_verified`
--
ALTER TABLE `tb_ss_verified`
  MODIFY `id_ss_verified` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_stock_hold`
--
ALTER TABLE `tb_stock_hold`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_stock_status`
--
ALTER TABLE `tb_stock_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_suplier`
--
ALTER TABLE `tb_suplier`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_surat_peringatan`
--
ALTER TABLE `tb_surat_peringatan`
  MODIFY `id_sp` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_tamu`
--
ALTER TABLE `tb_tamu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_tamu_lby`
--
ALTER TABLE `tb_tamu_lby`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_terima_paket`
--
ALTER TABLE `tb_terima_paket`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_tim`
--
ALTER TABLE `tb_tim`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_tmp_detaildo`
--
ALTER TABLE `tb_tmp_detaildo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_tmp_do`
--
ALTER TABLE `tb_tmp_do`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_tmp_lap_distribusi`
--
ALTER TABLE `tb_tmp_lap_distribusi`
  MODIFY `id_lap_dis` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_tmp_mutasi`
--
ALTER TABLE `tb_tmp_mutasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_truck`
--
ALTER TABLE `tb_truck`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_truk`
--
ALTER TABLE `tb_truk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_users`
--
ALTER TABLE `tb_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_user_level_mapping`
--
ALTER TABLE `tb_user_level_mapping`
  MODIFY `id_mapping` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_whats`
--
ALTER TABLE `tb_whats`
  MODIFY `id_what` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_wilayah`
--
ALTER TABLE `tb_wilayah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `trashbin_do`
--
ALTER TABLE `trashbin_do`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbhrd_nilai_lingkungan`
--
ALTER TABLE `tbhrd_nilai_lingkungan`
  ADD CONSTRAINT `tbhrd_nilai_lingkungan_fk_location` FOREIGN KEY (`location_id`) REFERENCES `tbhrd_lokasi` (`id`),
  ADD CONSTRAINT `tbhrd_nilai_lingkungan_fk_rating` FOREIGN KEY (`rating_id`) REFERENCES `tbhrd_issue_rating` (`id`),
  ADD CONSTRAINT `tbhrd_nilai_lingkungan_fk_status` FOREIGN KEY (`status_id`) REFERENCES `tbhrd_issue_status` (`id`);

--
-- Ketidakleluasaan untuk tabel `tbkeu_akun`
--
ALTER TABLE `tbkeu_akun`
  ADD CONSTRAINT `fk_tbkeu_akun_klasifikasi` FOREIGN KEY (`id_klasifikasi`) REFERENCES `tbkeu_klasifikasi_akun` (`id_klasifikasi`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbkeu_akun_parent` FOREIGN KEY (`parent_id`) REFERENCES `tbkeu_akun` (`id_akun`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_akun_karismaerp_ref`
--
ALTER TABLE `tbkeu_akun_karismaerp_ref`
  ADD CONSTRAINT `fk_tbkeu_akun_karismaerp_akun` FOREIGN KEY (`id_akun`) REFERENCES `tbkeu_akun` (`id_akun`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbkeu_akun_karismaerp_batch` FOREIGN KEY (`id_batch`) REFERENCES `tbkeu_karismaerp_import_batch` (`id_batch`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbkeu_akun_karismaerp_sub` FOREIGN KEY (`id_sub_klasifikasi`) REFERENCES `tbkeu_sub_klasifikasi_akun` (`id_sub_klasifikasi`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_jurnal`
--
ALTER TABLE `tbkeu_jurnal`
  ADD CONSTRAINT `fk_tbkeu_jurnal_jenis` FOREIGN KEY (`id_jenis_jurnal`) REFERENCES `tbkeu_jenis_jurnal` (`id_jenis_jurnal`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbkeu_jurnal_periode` FOREIGN KEY (`id_periode`) REFERENCES `tbkeu_periode_fiskal` (`id_periode`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbkeu_jurnal_reversal` FOREIGN KEY (`reversal_of_journal_id`) REFERENCES `tbkeu_jurnal` (`id_jurnal`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_jurnal_detail`
--
ALTER TABLE `tbkeu_jurnal_detail`
  ADD CONSTRAINT `fk_tbkeu_jurnal_detail_akun` FOREIGN KEY (`id_akun`) REFERENCES `tbkeu_akun` (`id_akun`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbkeu_jurnal_detail_jurnal` FOREIGN KEY (`id_jurnal`) REFERENCES `tbkeu_jurnal` (`id_jurnal`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_jurnal_log`
--
ALTER TABLE `tbkeu_jurnal_log`
  ADD CONSTRAINT `fk_tbkeu_jurnal_log_jurnal` FOREIGN KEY (`id_jurnal`) REFERENCES `tbkeu_jurnal` (`id_jurnal`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_karismaerp_kelompok_jurnal`
--
ALTER TABLE `tbkeu_karismaerp_kelompok_jurnal`
  ADD CONSTRAINT `fk_tbkeu_karismaerp_kelompok_batch` FOREIGN KEY (`id_batch`) REFERENCES `tbkeu_karismaerp_import_batch` (`id_batch`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_kas_keluar_detail`
--
ALTER TABLE `tbkeu_kas_keluar_detail`
  ADD CONSTRAINT `tbkeu_kas_keluar_detail_ibfk_1` FOREIGN KEY (`id_kas_keluar`) REFERENCES `tbkeu_kas_keluar` (`id_kas_keluar`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_kas_masuk_detail`
--
ALTER TABLE `tbkeu_kas_masuk_detail`
  ADD CONSTRAINT `tbkeu_kas_masuk_detail_ibfk_1` FOREIGN KEY (`id_kas_masuk`) REFERENCES `tbkeu_kas_masuk` (`id_kas_masuk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_mapping_akun`
--
ALTER TABLE `tbkeu_mapping_akun`
  ADD CONSTRAINT `fk_tbkeu_mapping_akun` FOREIGN KEY (`id_akun`) REFERENCES `tbkeu_akun` (`id_akun`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_pembayaran`
--
ALTER TABLE `tbkeu_pembayaran`
  ADD CONSTRAINT `fk_tbkeu_pembayaran_jurnal` FOREIGN KEY (`id_jurnal`) REFERENCES `tbkeu_jurnal` (`id_jurnal`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_pembayaran_alokasi`
--
ALTER TABLE `tbkeu_pembayaran_alokasi`
  ADD CONSTRAINT `fk_tbkeu_payment_alloc_payment` FOREIGN KEY (`id_pembayaran`) REFERENCES `tbkeu_pembayaran` (`id_pembayaran`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_periode_fiskal_log`
--
ALTER TABLE `tbkeu_periode_fiskal_log`
  ADD CONSTRAINT `fk_tbkeu_periode_log_periode` FOREIGN KEY (`id_periode`) REFERENCES `tbkeu_periode_fiskal` (`id_periode`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_posting_exception`
--
ALTER TABLE `tbkeu_posting_exception`
  ADD CONSTRAINT `fk_tbkeu_exception_jurnal` FOREIGN KEY (`id_jurnal`) REFERENCES `tbkeu_jurnal` (`id_jurnal`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_report_rule_akun`
--
ALTER TABLE `tbkeu_report_rule_akun`
  ADD CONSTRAINT `fk_tbkeu_report_rule_akun` FOREIGN KEY (`id_akun`) REFERENCES `tbkeu_akun` (`id_akun`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbkeu_report_rule_batch` FOREIGN KEY (`id_batch`) REFERENCES `tbkeu_karismaerp_import_batch` (`id_batch`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_saldo_awal_akun`
--
ALTER TABLE `tbkeu_saldo_awal_akun`
  ADD CONSTRAINT `fk_tbkeu_saldo_awal_akun` FOREIGN KEY (`id_akun`) REFERENCES `tbkeu_akun` (`id_akun`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbkeu_sub_klasifikasi_akun`
--
ALTER TABLE `tbkeu_sub_klasifikasi_akun`
  ADD CONSTRAINT `fk_tbkeu_sub_klasifikasi_batch` FOREIGN KEY (`id_batch`) REFERENCES `tbkeu_karismaerp_import_batch` (`id_batch`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbkeu_sub_klasifikasi_header` FOREIGN KEY (`id_akun_header`) REFERENCES `tbkeu_akun` (`id_akun`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbkeu_sub_klasifikasi_klasifikasi` FOREIGN KEY (`id_klasifikasi`) REFERENCES `tbkeu_klasifikasi_akun` (`id_klasifikasi`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbpo_formula_result`
--
ALTER TABLE `tbpo_formula_result`
  ADD CONSTRAINT `tbpo_formula_result_ibfk_1` FOREIGN KEY (`id_formula`) REFERENCES `tbpo_formula` (`id_formula`);

--
-- Ketidakleluasaan untuk tabel `tbpo_formula_variable`
--
ALTER TABLE `tbpo_formula_variable`
  ADD CONSTRAINT `tbpo_formula_variable_ibfk_1` FOREIGN KEY (`id_formula`) REFERENCES `tbpo_formula` (`id_formula`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbso_cancel_partial_request`
--
ALTER TABLE `tbso_cancel_partial_request`
  ADD CONSTRAINT `fk_cpreq_id_so` FOREIGN KEY (`id_so`) REFERENCES `tbso_sales_order` (`id_so`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cpreq_id_so_detail` FOREIGN KEY (`id_so_detail`) REFERENCES `tbso_sales_order_detail` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbso_faktur_jurnal`
--
ALTER TABLE `tbso_faktur_jurnal`
  ADD CONSTRAINT `fk_faktur_jurnal_id` FOREIGN KEY (`id_faktur`) REFERENCES `tbso_faktur_penjualan` (`id_faktur`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_checklist_kendaraan_detail`
--
ALTER TABLE `tb_checklist_kendaraan_detail`
  ADD CONSTRAINT `tb_checklist_kendaraan_detail_ibfk_1` FOREIGN KEY (`checklist_id`) REFERENCES `tb_checklist_kendaraan` (`id`);

--
-- Ketidakleluasaan untuk tabel `tb_gudang_wilayah`
--
ALTER TABLE `tb_gudang_wilayah`
  ADD CONSTRAINT `tb_gudang_wilayah_ibfk_1` FOREIGN KEY (`id_gudang`) REFERENCES `tb_gudang` (`id_gudang`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_indikator_hows`
--
ALTER TABLE `tb_indikator_hows`
  ADD CONSTRAINT `tb_indikator_hows_ibfk_1` FOREIGN KEY (`id_how`) REFERENCES `tb_hows` (`id_how`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_indikator_whats`
--
ALTER TABLE `tb_indikator_whats`
  ADD CONSTRAINT `tb_indikator_whats_ibfk_1` FOREIGN KEY (`id_what`) REFERENCES `tb_whats` (`id_what`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_kpi_verified`
--
ALTER TABLE `tb_kpi_verified`
  ADD CONSTRAINT `tb_kpi_verified_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_users` (`id`),
  ADD CONSTRAINT `tb_kpi_verified_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `tb_users` (`id`);

--
-- Ketidakleluasaan untuk tabel `tb_pengajuan_od_faktur`
--
ALTER TABLE `tb_pengajuan_od_faktur`
  ADD CONSTRAINT `tb_pengajuan_od_faktur_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `tb_pengajuan_od` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_surat_peringatan`
--
ALTER TABLE `tb_surat_peringatan`
  ADD CONSTRAINT `fk_sp_user` FOREIGN KEY (`id_user`) REFERENCES `tb_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
