-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 25 Apr 2026 pada 08.49
-- Versi server: 10.4.25-MariaDB
-- Versi PHP: 7.4.30

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
-- Struktur dari tabel `tbso_sales_order`
--

CREATE TABLE `tbso_sales_order` (
  `id_so` int(11) NOT NULL,
  `no_so` varchar(30) DEFAULT NULL,
  `no_faktur` varchar(30) DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `customer_id` varchar(30) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `gudang_id` varchar(30) NOT NULL,
  `jumlah_item` int(11) NOT NULL DEFAULT 0,
  `total_tonase` decimal(15,3) NOT NULL DEFAULT 0.000,
  `total_kubikasi` decimal(15,5) NOT NULL DEFAULT 0.00000,
  `batas_tonase` decimal(15,3) DEFAULT NULL,
  `batas_kubikasi` decimal(15,5) DEFAULT NULL,
  `status` enum('draft','waiting_approval','approved','partial_delivered','completed','cancelled') NOT NULL DEFAULT 'draft',
  `is_nego` tinyint(1) NOT NULL DEFAULT 0,
  `approve_by` varchar(100) DEFAULT NULL,
  `approval_by` varchar(50) DEFAULT NULL,
  `approval_at` datetime DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `create_by` varchar(50) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `update_by` varchar(50) DEFAULT NULL,
  `update_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tbso_sales_order`
--

INSERT INTO `tbso_sales_order` (`id_so`, `no_so`, `no_faktur`, `tanggal_transaksi`, `customer_id`, `customer_name`, `gudang_id`, `jumlah_item`, `total_tonase`, `total_kubikasi`, `batas_tonase`, `batas_kubikasi`, `status`, `is_nego`, `approve_by`, `approval_by`, `approval_at`, `catatan`, `create_by`, `create_at`, `update_by`, `update_at`) VALUES
(1, '123', 'INV220420260001', '2026-04-22', '1578', 'Aba, Toko', '2', 1, 0.025, 0.03744, 6.000, 9.00000, 'cancelled', 0, NULL, NULL, NULL, '', 'kiusc', '2026-04-22 12:54:35', 'kiusc', '2026-04-22 12:57:10'),
(2, '123', 'INV230420260001', '2026-04-23', '1578', 'Aba, Toko', '2', 1, 0.013, 0.01872, 6.000, 9.00000, 'draft', 0, NULL, NULL, NULL, '', 'kiusc', '2026-04-23 12:56:15', 'kiusc', '2026-04-23 12:59:28'),
(3, '123', 'INV230420260002', '2026-04-23', '7004', 'Aba Jaya, Kios', '2', 1, 0.013, 0.01872, 6.000, 9.00000, 'draft', 0, NULL, NULL, NULL, '', 'kiusc', '2026-04-23 13:01:02', NULL, '2026-04-23 13:01:02'),
(4, '123', 'INV230420260003', '2026-04-23', '5925', 'Ace Bio Care, PT', '2', 2, 0.038, 0.05617, 6.000, 9.00000, 'draft', 0, NULL, NULL, NULL, '', 'kiusc', '2026-04-23 13:07:32', 'kiusc', '2026-04-23 13:38:10'),
(5, '124', 'INV230420260004', '2026-04-23', '4564', 'Aboe Tani', '2', 1, 0.013, 0.01872, 6.000, 9.00000, 'draft', 0, NULL, NULL, NULL, '', 'kiusc', '2026-04-23 13:09:33', NULL, '2026-04-23 13:09:33'),
(7, '444', 'INV230420260005', '2026-04-23', '383', 'A Hafidh Mudarrisiy', '2', 1, 0.013, 0.01872, 6.000, 9.00000, 'draft', 0, NULL, NULL, NULL, '', 'kiusc', '2026-04-23 16:02:04', NULL, '2026-04-23 16:02:04');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `tbso_sales_order`
--
ALTER TABLE `tbso_sales_order`
  ADD PRIMARY KEY (`id_so`),
  ADD KEY `idx_so_status` (`status`),
  ADD KEY `idx_so_customer` (`customer_id`),
  ADD KEY `idx_so_tanggal` (`tanggal_transaksi`),
  ADD KEY `idx_so_no_so` (`no_so`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbso_sales_order`
--
ALTER TABLE `tbso_sales_order`
  MODIFY `id_so` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
