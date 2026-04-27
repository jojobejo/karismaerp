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
-- Struktur dari tabel `tbso_sales_order_detail`
--

CREATE TABLE `tbso_sales_order_detail` (
  `id` int(11) NOT NULL,
  `no_so` varchar(30) NOT NULL,
  `kd_po` varchar(100) DEFAULT NULL,
  `no_faktur` varchar(30) DEFAULT NULL,
  `produk_id` varchar(50) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nama_barang` varchar(200) NOT NULL,
  `qty` decimal(15,3) NOT NULL,
  `qty_box` decimal(15,3) NOT NULL DEFAULT 0.000,
  `qty_satuan` decimal(15,3) NOT NULL DEFAULT 0.000,
  `isi_per_box` int(11) NOT NULL DEFAULT 1,
  `qty_delivered` decimal(15,3) NOT NULL DEFAULT 0.000,
  `satuan` varchar(20) NOT NULL,
  `expired_date` date NOT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `pajak` decimal(5,2) NOT NULL DEFAULT 0.00,
  `disc` decimal(5,2) NOT NULL DEFAULT 0.00,
  `subtotal_before_disc` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal_after_disc` decimal(15,2) NOT NULL DEFAULT 0.00,
  `hrg_satuan` decimal(18,2) NOT NULL,
  `hrg_pokok` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total_harga` decimal(18,2) NOT NULL,
  `tonase_satuan` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `kubikasi_satuan` decimal(10,6) NOT NULL DEFAULT 0.000000,
  `berat_gram` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `kubikasi_m3` decimal(12,6) NOT NULL DEFAULT 0.000000,
  `kode_akun` varchar(20) DEFAULT NULL,
  `is_nego` tinyint(1) NOT NULL DEFAULT 0,
  `approve_by` varchar(255) DEFAULT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `create_by` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tbso_sales_order_detail`
--

INSERT INTO `tbso_sales_order_detail` (`id`, `no_so`, `kd_po`, `no_faktur`, `produk_id`, `kd_barang`, `nama_barang`, `qty`, `qty_box`, `qty_satuan`, `isi_per_box`, `qty_delivered`, `satuan`, `expired_date`, `no_lot`, `pajak`, `disc`, `subtotal_before_disc`, `subtotal_after_disc`, `hrg_satuan`, `hrg_pokok`, `total_harga`, `tonase_satuan`, `kubikasi_satuan`, `berat_gram`, `kubikasi_m3`, `kode_akun`, `is_nego`, `approve_by`, `create_at`, `create_by`) VALUES
(4, '123', NULL, 'INV230420260001', 'QABAC01', 'QABAC01', 'Abacel 18 EC 10 X 1 ltr', 10.000, 1.000, 0.000, 10, 0.000, 'Btl', '2028-07-01', '', 11.00, 5.00, 300000.00, 285000.00, 30000.00, 30000.00, 316350.00, 0.0000, 0.000000, 1253.0000, 0.001872, '', 0, NULL, '2026-04-23 12:59:28', 'kiusc'),
(5, '123', NULL, 'INV230420260002', '', 'QABAC01', 'Abacel 18 EC 10 X 1 ltr', 10.000, 1.000, 0.000, 10, 0.000, 'Btl', '2028-06-01', '', 11.00, 10.00, 300000.00, 270000.00, 30000.00, 30000.00, 299700.00, 0.0000, 0.000000, 1253.0000, 0.001872, '', 0, NULL, '2026-04-23 13:01:02', 'kiusc'),
(7, '124', NULL, 'INV230420260004', '', 'QABAC01', 'Abacel 18 EC 10 X 1 ltr', 10.000, 1.000, 0.000, 10, 0.000, 'Btl', '2028-06-01', '', 0.00, 0.00, 300000.00, 300000.00, 30000.00, 30000.00, 300000.00, 0.0000, 0.000000, 1253.0000, 0.001872, '', 0, NULL, '2026-04-23 13:09:33', 'kiusc'),
(9, '', NULL, 'INV230420260003', 'QABAC01', 'QABAC01', 'Abacel 18 EC 10 X 1 ltr', 20.000, 2.000, 0.000, 10, 0.000, 'Btl', '2028-06-01', '', 0.00, 1.00, 600000.00, 594000.00, 30000.00, 30000.00, 594000.00, 0.0000, 0.000000, 1253.0000, 0.001872, '', 0, NULL, '2026-04-23 13:38:10', 'kiusc'),
(10, '', NULL, 'INV230420260003', '', 'QABAC01', 'Abacel 18 EC 10 X 1 ltr', 10.000, 1.000, 0.000, 10, 0.000, 'Btl', '2028-06-01', '', 0.00, 0.00, 300000.00, 300000.00, 30000.00, 30000.00, 300000.00, 0.0000, 0.000000, 1253.0000, 0.001872, '', 0, NULL, '2026-04-23 13:38:10', 'kiusc'),
(11, '', NULL, 'INV230420260005', '', 'QABAC01', 'Abacel 18 EC 10 X 1 ltr', 10.000, 1.000, 0.000, 10, 0.000, 'Btl', '2028-07-01', '', 0.00, 0.00, 3000000.00, 3000000.00, 300000.00, 30000.00, 3000000.00, 0.0000, 0.000000, 1253.0000, 0.001872, '', 0, 'Admin Distribusi', '2026-04-23 16:02:04', 'kiusc');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `tbso_sales_order_detail`
--
ALTER TABLE `tbso_sales_order_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sod_idso` (`no_so`),
  ADD KEY `idx_sod_barang` (`kd_barang`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbso_sales_order_detail`
--
ALTER TABLE `tbso_sales_order_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
