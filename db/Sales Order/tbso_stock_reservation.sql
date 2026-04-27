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
-- Struktur dari tabel `tbso_stock_reservation`
--

CREATE TABLE `tbso_stock_reservation` (
  `id` int(11) NOT NULL,
  `no_so` varchar(30) NOT NULL,
  `no_faktur` varchar(30) DEFAULT NULL,
  `id_so_detail` int(11) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `exp_date` varchar(15) NOT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `gudang_id` varchar(30) NOT NULL,
  `qty_reserved` decimal(15,3) NOT NULL,
  `status` enum('active','released') NOT NULL DEFAULT 'active',
  `create_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tbso_stock_reservation`
--

INSERT INTO `tbso_stock_reservation` (`id`, `no_so`, `no_faktur`, `id_so_detail`, `kd_barang`, `exp_date`, `no_lot`, `gudang_id`, `qty_reserved`, `status`, `create_at`) VALUES
(1, '123', 'INV220420260001', 1, 'QABAC01', '01/06/2028', '', '2', 10.000, 'released', '2026-04-22 12:54:35'),
(2, '123', 'INV220420260001', 2, 'QABAC01', '01/06/2028', '', '2', 20.000, 'released', '2026-04-22 12:56:19'),
(3, '123', 'INV230420260001', 3, 'QABAC01', '01/07/2028', '', '2', 10.000, 'released', '2026-04-23 12:56:15'),
(4, '123', 'INV230420260001', 4, 'QABAC01', '01/07/2028', '', '2', 10.000, 'active', '2026-04-23 12:59:28'),
(5, '123', 'INV230420260002', 5, 'QABAC01', '01/06/2028', '', '2', 10.000, 'active', '2026-04-23 13:01:02'),
(6, '123', 'INV230420260003', 6, 'QABAC01', '01/06/2028', '', '2', 10.000, 'released', '2026-04-23 13:07:32'),
(7, '124', 'INV230420260004', 7, 'QABAC01', '01/06/2028', '', '2', 10.000, 'active', '2026-04-23 13:09:33'),
(8, '', 'INV230420260003', 8, 'QABAC01', '01/06/2028', '', '2', 20.000, 'released', '2026-04-23 13:37:23'),
(9, '', 'INV230420260003', 9, 'QABAC01', '01/06/2028', '', '2', 20.000, 'active', '2026-04-23 13:38:10'),
(10, '', 'INV230420260003', 10, 'QABAC01', '01/06/2028', '', '2', 10.000, 'active', '2026-04-23 13:38:10'),
(11, '', 'INV230420260005', 11, 'QABAC01', '01/07/2028', '', '2', 10.000, 'active', '2026-04-23 16:02:04');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `tbso_stock_reservation`
--
ALTER TABLE `tbso_stock_reservation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_res_so` (`no_so`),
  ADD KEY `idx_res_barang` (`kd_barang`,`exp_date`,`gudang_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbso_stock_reservation`
--
ALTER TABLE `tbso_stock_reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
